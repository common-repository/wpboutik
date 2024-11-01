<?php 
 $title_tag = (!empty($args['title_tag'])) ? $args['title_tag'] : 'h3';
 $link = (isset($args['link'])) ? $args['link'] : true;
 $classes = (!empty($args['class'])) ? 'product-title '.$args['class'] : 'product-title';
?>

<?= '<'.$title_tag.' class="'.$classes.'">' ?>
  <?php if ($link) : ?>
    <a class="product-card-permalink" href="<?php the_permalink(); ?>">
      <?php the_title(); ?>
    </a>
    <?php else :
      the_title();
    endif; ?>
<?= '</'.$title_tag.'>' ?>
