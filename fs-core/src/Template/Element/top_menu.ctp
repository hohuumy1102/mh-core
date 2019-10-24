<li class="dropdown user user-menu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="ion ion-person"></i>
        <span class="hidden-xs">
            <?php $name = $this->request->session()->read('Auth.Backend.User.firstname') . ' ' . $this->request->session()->read('Auth.Backend.User.lastname'); ?>
            <?php $name = ($name == ' ' ? $this->request->session()->read('Auth.Backend.User.email') : $name); ?>
            <?= $name; ?>
        </span>
    </a>
    <ul class="dropdown-menu">
        <!-- Menu Footer-->
        <li class="user-footer">
            <div class="pull-left">
                <?= $this->Html->link(__('Profile'), ['controller' => 'AdminUsers', 'action' => 'view', $this->request->session()->read('Auth.Backend.User.id')], ['class' => 'btn btn-default btn-flat']) ?>
            </div>
            <div class="pull-right">
                <a class="btn btn-default btn-flat" href="<?= $this->Url->build(['controller' => 'AdminUsers', 'action' => 'logout']); ?>"><?php echo __('Logout'); ?></a>
            </div>
        </li>
    </ul>
</li>
