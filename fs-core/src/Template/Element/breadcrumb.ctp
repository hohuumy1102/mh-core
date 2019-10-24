<h1><?php echo $headerTitle; ?></h1>
<ol class="breadcrumb">
    <?php foreach ($breadcrumb as $index => $section): ?>
        <li class="<?php echo ($index == count($breadcrumb) - 1 ? 'active' : ''); ?>">
            <a class="<?php echo $section['class']; ?>" href="<?php echo $section['href']; ?>"><?php echo $section['title']; ?></a>
        </li>
    <?php endforeach; ?>
</ol>
