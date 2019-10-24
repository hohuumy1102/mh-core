<ul class="sidebar-menu">
    <?php foreach ($menuList as $navSection): ?>
        <li class="header"><?php echo __($navSection['label']); ?></li>

        <?php foreach ($navSection['subNavs'] as $module => $moduleInfo): ?>
        <li class="<?= ($controller == $module ? 'active' : ''); ?> <?= (!empty($moduleInfo['subNavs']) ? 'treeview' : ''); ?>">
            <a href="<?= $moduleInfo['url']; ?>">
                <i class="fa fa-<?php echo $moduleInfo['icon']; ?>"></i>
                <span><?php echo __($moduleInfo['label']); ?></span>
                <?php if (!empty($moduleInfo['subNavs'])): ?>
                    <i class="fa fa-angle-left pull-right"></i>
                <?php endif; ?>
            </a>
            <?php if (!empty($moduleInfo['subNavs'])): ?>
                <ul class="treeview-menu <?= (!empty($moduleInfo['subNavs']) ? 'menu-open' : ''); ?>">
                    <?php foreach ($moduleInfo['subNavs'] as $navInfo): ?>
                        <li class="<?= $navInfo['class']; ?>">
                            <a href="<?= $navInfo['url']; ?>">
                                <i class="fa fa-circle-o"></i>
                                <?= __($navInfo['label']); ?>
                                <?php if (!empty($navInfo['box'])): ?>
                                    <small class="label pull-right bg-red"><?php echo $navInfo['box']; ?></small>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    <?php endforeach; ?>
</ul>
