<?php

namespace FsCore\Controller\Component;

use FsCore\Utility\Utils;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Cake\Utility\Hash;

class FacebookComponent extends Component {

    protected $fb;

    const SYNC_PHOTO_RESET = 0;
    const SYNC_PHOTO_MORE = 1;
    const SYNC_PHOTO_DATABASE = 2;

    public function initialize(array $config) {
        parent::initialize($config);

        Utils::useComponents($this, ['Auth']);

        $this->fb = new Facebook(Configure::read('FacebookApp'));

        if (!empty($this->Auth->user('fb_access_token'))) {
            $this->setAccessToken($this->Auth->user('fb_access_token'));
        }
    }

    public function setAccessToken($fbAccessToken = null) {
        if (!empty($fbAccessToken)) {
            $this->fb->setDefaultAccessToken($fbAccessToken);
        }
    }

    public function getAlbumByName($fbAlbums = [], $name = '') {
        $selfieAlbum = [];

        if (!empty($fbAlbums['data'])) {
            $selfieAlbum = Collection($fbAlbums['data'])->match(['name' => $name])->toArray();
        }

        return $selfieAlbum;
    }

    /**
     * get Facebook Albums
     * @param  array $options [fields, limit]
     * @param  string  $afterCursor next page
     * @return array result
     */
    public function getAlbums($options = [], $afterCursor = null) {
        try {
            $options += [
                'fields' => 'id, name, picture',
                'limit' => 10
            ];

            $fields = $options['fields'];
            $limit = $options['limit'];

            $request = "/me/albums?fields=$fields&limit={$limit}";

            if (!empty($afterCursor)) {
                $request .= "&after={$afterCursor}";
            }

            $response = $this->fb->get($request);

            $responseEdge = $response->getGraphEdge();

            $afterCursor = $responseEdge->getMetaData();
            $afterCursor = !empty($afterCursor['paging']['cursors']['after']) ? $afterCursor['paging']['cursors']['after'] : 0;

            $fbAlbums = $responseEdge->asArray();
        } catch (FacebookResponseException $e) {
            return [
                'status' => 1,
                'message' => $e->getMessage()
            ];
        } catch (FacebookSDKException $e) {
            return [
                'status' => 1,
                'message' => $e->getMessage()
            ];
        }

        return [
            'status' => 0,
            'data' => $fbAlbums,
            'afterCursor' => $afterCursor
        ];
    }

    /**
     * get Facebook photos of album
     * @param  integer  $albumId     album id
     * @param  array  $options [fields, type, limit]
     * @param  string  $afterCursor next page
     * @return array result
     */
    public function getPhotos($albumId = null, $options = [], $afterCursor = null) {
        try {
            $options += [
                'fields' => 'id, name, source, images, picture, tags, width, height',
                'type' => 'uploaded',
                'limit' => 20,
                'since' => '',
                'until' => '',
                'force' => false //don't get data from until_date if no data return
            ];

            if (empty($albumId)) {
                $albumId = 'me';
            }

            $fields = $options['fields'];
            $type = $options['type'];
            $limit = $options['limit'];
            $since = $options['since'];
            $until = $options['until'];
            $force = $options['force'];

            $loop = true;

            while ($loop) {
                $request = "/$albumId/photos?fields=$fields&type=$type&limit=$limit&since=$since&until=$until";

                if (!empty($afterCursor)) {
                    $request .= "&after={$afterCursor}";
                }

                $response = $this->fb->get($request);

                $responseEdge = $response->getGraphEdge();

                $afterCursor = '';
                $metadata = $responseEdge->getMetaData();
                if (!empty($metadata)) {
                    $afterCursor = $metadata['paging']['cursors']['after'];
                }

                $fbPhotos = $responseEdge->asArray();

                $loop = false;
                if (empty($fbPhotos) && $force) {
                    $loop = true;
                    $force = false;
                    $since = $until;
                    $until = '';
                }
            }
        } catch (FacebookResponseException $e) {
            return [
                'status' => 1,
                'message' => $e->getMessage()
            ];
        } catch (FacebookSDKException $e) {
            return [
                'status' => 1,
                'message' => $e->getMessage()
            ];
        }

        return [
            'status' => 0,
            'data' => $fbPhotos,
            'afterCursor' => $afterCursor
        ];
    }

    /**
     * get Facebook profile
     * @param  array $fbUserIds array of facebook user id
     * @param  string $fields fields
     * @return array result
     */
    public function getProfiles($fbUserIds, $fields = '') {
        if (!is_array($fbUserIds)) {
            $fbUserIds = [$fbUserIds];
        }

        $defaultFields = 'id,email,name,picture.width(400).height(400)';

        if ($fields != '') {
            $defaultFields .= ',' . $fields;
        }

        $requestList = [];
        foreach ($fbUserIds as $fbId) {
            $requestList[$fbId] = $this->fb->request('GET', "/{$fbId}?fields=$defaultFields");
        }
        try {
            $batchResponse = $this->fb->sendBatchRequest($requestList);
        } catch (FacebookResponseException $e) {
            return [
                'status' => 1,
                'message' => $e->getMessage()
            ];
        } catch (FacebookSDKException $e) {
            return [
                'status' => 1,
                'message' => $e->getMessage()
            ];
        }

        $data = [];
        foreach ($batchResponse->getResponses() as $fbUserId => $reponse) {
            $data[$fbUserId] = $reponse->getGraphUser()->asArray();
            if (isset($data[$fbUserId]['error'])) {
                unset($data[$fbUserId]);
            }
        }

        return [
            'status' => 0,
            'data' => $data
        ];
    }

    /**
     * format photos
     * @param  array $fbPhotos photos
     * @return array formated photos
     */
    public function formatPhotos($fbPhotos = [], $type = 'scale', $options = []) {
        $formatPhotos = [];

        $options += [
            'minScale' => 0.5,
            'maxScale' => 2,
            'minHeight' => 900,
            'maxHeight' => 500,
            'minWidth' => 500,
            'maxWidth' => 100
        ];

        if (!empty($fbPhotos['data'])) {
            foreach ($fbPhotos['data'] as $photo) {
                $photoId = $photo['id'];

                foreach ($photo['images'] as $image) {
                    if ($type == 'scale') {
                        $scale = $image['height'] / $image['width'];
                        if ($options['minScale'] <= $scale && $scale <= $options['maxScale']) {
                            $formatPhotos[$photoId] = $image['source'];
                            break;
                        }
                    }
                }
                if (isset($formatPhotos[$photoId])) {
                    continue;
                }
            }
        }

        return $formatPhotos;
    }

    /**
     * get liked pages
     * @return array result
     */
    public function getLikedPages() {
        try {
            $request = "/me/likes";
            $response = $this->fb->get($request);
            $responseEdge = $response->getGraphEdge();
            $pageList = $responseEdge->asArray();
        } catch (FacebookResponseException $e) {
            return [
                'status' => 1,
                'message' => $e->getMessage()
            ];
        } catch (FacebookSDKException $e) {
            return [
                'status' => 1,
                'message' => $e->getMessage()
            ];
        }
        return $pageList;
    }

    /**
     * get Facebook Videos
     * @param  string  $type        tagged, uploaded
     * @param  array  $options [fields, limit]
     * @param  string  $afterCursor next paging
     * @return array result
     */
    public function getVideos($type = 'uploaded', $options = [], $afterCursor = null) {
        try {
            $options += [
                'fields' => 'id,description,source,length,tags,thumbnails{uri}',
                'limit' => 10,
                'since' => '',
                'until' => ''
            ];

            $fields = $options['fields'];
            $limit = $options['limit'];
            $since = $options['since'];
            $until = $options['until'];

            $request = "/me/videos?fields=$fields&limit=$limit&type=$type&since=$since&until=$until";

            if (!empty($afterCursor)) {
                $request .= "&after={$afterCursor}";
            }

            $response = $this->fb->get($request);

            $responseEdge = $response->getGraphEdge();

            $afterCursor = $responseEdge->getMetaData();
            $afterCursor = $afterCursor['paging']['cursors']['after'];

            $fbVideos = $responseEdge->asArray();
        } catch (FacebookResponseException $e) {
            return [
                'status' => 1,
                'message' => $e->getMessage()
            ];
        } catch (FacebookSDKException $e) {
            return [
                'status' => 1,
                'message' => $e->getMessage()
            ];
        }

        return [
            'status' => 0,
            'data' => $fbVideos,
            'afterCursor' => $afterCursor
        ];
    }

    /**
     * post feed on wall
     * @param  array  $data [link, message, tags, place]
     * @return array result
     */
    public function postFeed($data = []) {
        try {
            $request = '/me/feed';

            $data += [
                'link' => '',
                'message' => '',
                'tags' => '',
                'place' => ''
            ];

            $response = $this->fb->post($request, $data);
        } catch (FacebookResponseException $e) {
            return [
                'status' => 1,
                'message' => $e->getMessage()
            ];
        } catch (FacebookSDKException $e) {
            return [
                'status' => 1,
                'message' => $e->getMessage()
            ];
        }

        return [
            'status' => 0,
            'data' => $response->getGraphNode()->asArray()
        ];
    }

    public function sortByTotalTag($fbPhotos = []) {
        uasort($fbPhotos['data'], function ($a, $b) {
            if (!empty($a['tags'])) {
                $a = count($a['tags']);
            } else {
                $a = 0;
            }

            if (!empty($b['tags'])) {
                $b = count($b['tags']);
            } else {
                $b = 0;
            }

            return ($a == $b) ? 0 : (($a < $b) ? 1 : - 1);
        });

        return $fbPhotos;
    }

    public function getPlacePhotos($fbPhotos = []) {
        $placePhotos = Collection($fbPhotos['data'])->filter(function ($photo, $key) {
            return !empty($photo['place']) && !empty($photo['place']['id']);
        });

        return $placePhotos->toArray();
    }

    public function renewToken($accessToken) {
        $clientId = Configure::read('FacebookApp.app_id');
        $clientSecret = Configure::read('FacebookApp.app_secret');
        try {
            $request = "/oauth/access_token?grant_type=fb_exchange_token&client_id={$clientId}&client_secret={$clientSecret}&fb_exchange_token={$accessToken}";
            $response = $this->fb->get($request);
            $node = $response->getGraphNode()->asArray();
        } catch (FacebookResponseException $e) {
            return false;
        } catch (FacebookSDKException $e) {
            return false;
        }
        return $node;
    }

}
