<?php use Cake\Core\Configure; ?>
<?php $languageList = Configure::read('LanguageList'); ?>
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <?= $this->element('FsCore.Crud/crud_main_nav') ?>

            <div class="box-body">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#common_fields" data-toggle="tab"><?php echo __('Common Fields'); ?></a>
                        </li>
                        <?php if (!empty($multiLangFields)): ?>
                            <?php foreach ($languageList as $languageCode => $languageLabel): ?>
                                <li>
                                    <a href="#language_<?php echo $languageCode; ?>" data-toggle="tab"><?php echo $languageLabel; ?></a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (!empty($extraTabs)): ?>
                            <?php foreach ($extraTabs as $tabCode => $tabContent): ?>
                                <li>
                                    <a href="#extra_<?php echo $tabCode; ?>" data-toggle="tab"><?php echo $tabContent['label']; ?></a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>

                    <div class="tab-content">
                        <div class="active tab-pane" id="common_fields">
                            <table class="table table-bordered table-striped wordWrap">
                                <tbody>
                                    <?php foreach ($object as $field => $value): ?>
                                        <?php if (strpos($field, 'language_') !== 0): ?>
                                        <tr>
                                            <th class="text-capitalize col-xs-4"><?= __(str_replace('_', ' ', $field)); ?></th>
                                            <td class="col-xs-8">
                                                <?php if (!empty($value['type']) && $value['type'] == 'photo'): ?>
                                                    <?php $photoPath = $this->Cf->imageUrl($value['value']); ?>
                                                    <a href="<?php echo $photoPath; ?>" class="thumbnail-link">
                                                        <img src="<?php echo $photoPath; ?>" width="100" />
                                                    </a>
                                                <?php elseif (!empty($value['type']) && $value['type'] == 'multi-photo'): ?>
                                                    <?php foreach ($value['value'] as $subPhoto): ?>
                                                        <?= $this->element('FsCore.Crud/multi_photo_item', ['photo' => $subPhoto, 'field' => $field]) ?>
                                                    <?php endforeach; ?>
                                                <?php elseif (!empty($value['type']) && $value['type'] == 'box'): ?>
                                                    <span class="<?php echo (!empty($value['class']) ? $value['class'] : ''); ?>"><?php echo $value['value']; ?></span>
                                                <?php elseif (!empty($value['type']) && $value['type'] == 'google-map'): ?>
                                                    <style type="text/css">
                                                        #map { height: 450px; }
                                                    </style>
                                                    <div id="map"></div>
                                                    <script type="text/javascript">
                                                        var map;
                                                        var geocoder;
                                                        var marker = null;
                                                        function initMap() {
                                                            geocoder = new google.maps.Geocoder();
                                                            setTimeout(function () {
                                                                updateMap({
                                                                    lat: <?php echo $value['value']['latitude']; ?>,
                                                                    lng: <?php echo $value['value']['longtitude']; ?>,
                                                                });
                                                            }, 1000);
                                                        }

                                                        function updateMap(center) {
                                                            map = new google.maps.Map(document.getElementById('map'), {
                                                                center: center,
                                                                zoom: 18,
                                                                mapTypeId: google.maps.MapTypeId.ROADMAP
                                                            });
                                                            updateMarker(map, map.center);
                                                        }

                                                        function updateMarker(map, location) {
                                                            if (marker) {
                                                                marker.setMap(null);
                                                            }
                                                            marker = null;
                                                            marker = new google.maps.Marker({
                                                                map: map,
                                                                draggable: true,
                                                                position: location
                                                            });
                                                        }
                                                    </script>
                                                    <script async defer
                                                            src="https://maps.googleapis.com/maps/api/js?key=<?php echo Configure::read('GoogleMap.ApiKey'); ?>&callback=initMap&libraries=places">
                                                    </script>
                                                <?php else: ?>
                                                    <?php echo $value['value']; ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (!empty($multiLangFields)): ?>
                            <?php foreach ($languageList as $languageCode => $languageLabel): ?>
                                <div class="tab-pane" id="language_<?php echo $languageCode; ?>">
                                    <table class="table table-bordered table-striped wordWrap">
                                        <tbody>
                                        <?php foreach ($multiLangFields as $field => $fieldInfo): ?>
                                            <?php $fieldStr = 'language_' . $field . '_' . $languageLabel; ?>
                                            <?php if (isset($object[$fieldStr])): ?>
                                            <tr>
                                                <th class="text-capitalize col-xs-4"><?= __(str_replace('_', ' ', $field)); ?></th>
                                                <td class="col-xs-8">
                                                    <?php if (!empty($object[$fieldStr]['type']) && $object[$fieldStr]['type'] == 'photo'): ?>
                                                        <?php $photoPath = $this->Cf->imageUrl($object[$fieldStr]['value']); ?>
                                                        <a href="<?php echo $photoPath; ?>" class="thumbnail-link">
                                                            <img src="<?php echo $photoPath; ?>" width="100" />
                                                        </a>
                                                    <?php else: ?>
                                                        <?php echo $object[$fieldStr]['value']; ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (!empty($extraTabs)): ?>
                            <?php foreach ($extraTabs as $tabCode => $tabContent): ?>
                                <div class="tab-pane" id="extra_<?php echo $tabCode; ?>">
                                    <?php echo $tabContent['content']; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("a.thumbnail-link").on("click", function (e) {
            $('#modal-image').find('.modal-body img').attr('src', $(this).attr('href'));
            $('#modal-image').modal('show');
            e.preventDefault();
        });
        $("i.glyphicon-trash").parents('a').on("click", function (e) {
            var result = confirm('<?php echo __('Are you sure?'); ?>');
            if (!result) {
                e.preventDefault();
                return result;
            }
        });
    });
</script>
