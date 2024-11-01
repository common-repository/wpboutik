<?php 
$wpboutik_show_slideshow_images = get_theme_mod( 'wpboutik_slideshow_images', true );

if ( $wpboutik_show_slideshow_images ) : ?>
  <div class="wpb-gallery-container slideshow">
    <h2 class="sr-only"><?php _e( 'Images', 'wpboutik' ); ?></h2>
    <?php
    if ( has_post_thumbnail() ) : ?>
      <div class="slide sl-1 relative imggalfirst">
        <?php the_post_thumbnail( 'large', array(
        'class' => 'h-full w-full object-contain',
        'alt'   => esc_html( get_the_title() )
        ) ); ?>
      </div>
        <?php
    else :
	    echo wpb_get_default_image('h-full w-full object-contain' );
    endif;
    $images = get_post_meta( get_the_ID(), 'galerie_images', true );
    if ( $images ) :
      $images = explode( ',', $images );
      $i  = 2;
      foreach ( $images as $img ) :
      //class="w-full h-[300px] object-cover"
      ?>
        <div class="slide sl-<?php echo $i; ?> relative imggalfirst">
          <?php echo wp_get_attachment_image( $img, 'large', false, array(
          'class' => 'h-full w-full object-contain',
          'alt'   => esc_html( get_the_title() . '-' . $i )
          ) ); ?>
          <!--<div class="absolute bottom-0 w-full px-5 py-3 bg-black/40 text-center text-white">Flower One Caption</div>-->
        </div>
        <?php
        $i ++;
      endforeach; ?>

      <!-- The previous button -->
      <a class="absolute left-0 top-1/2 p-4 -translate-y-1/2 bg-black/30 hover:bg-black/50 text-white hover:text-amber-500 cursor-pointer"
      onclick="moveSlide(-1)">❮</a>

      <!-- The next button -->
      <a class="absolute right-0 top-1/2 p-4 -translate-y-1/2 bg-black/30 hover:bg-black/50 text-white hover:text-amber-500 cursor-pointer"
      onclick="moveSlide(1)">❯</a>

    <?php endif; ?>

  </div>

  <script>
    // set the default active slide to the first one
    let slideIndex = 1;
    showSlide(slideIndex);

    // change slide with the prev/next button
    function moveSlide(moveStep) {
    showSlide(slideIndex += moveStep);
    }

    // change slide with the dots
    function currentSlide(n) {
    showSlide(slideIndex = n);
    }

    function showSlide(n) {
      let i;
      const slides = document.getElementsByClassName("slide");
      //const dots = document.getElementsByClassName('dot');

      if (n > slides.length) {
        slideIndex = 1
      }
      if (n < 1) {
        slideIndex = slides.length
      }

      // hide all slides
      for (i = 0; i < slides.length; i++) {
        slides[i].classList.add('hidden');
      }

      var slidess = document.querySelectorAll('.slide');
      slidess.forEach(function (slide) {
        slide.parentElement.classList.add('hidden');
      });

      // Afficher l'élément correspondant à la classe "sl-slideIndex"
      var activeSlide = document.querySelector('.sl-' + slideIndex);
      if (activeSlide) {
        activeSlide.parentElement.classList.remove('hidden');
      }

      // remove active status from all dots
      /*for (i = 0; i < dots.length; i++) {
      dots[i].classList.remove('bg-yellow-500');
      dots[i].classList.add('bg-green-600');
      }*/

      // show the active slide
      slides[slideIndex - 1].classList.remove('hidden');

      // highlight the active dot
      /*dots[slideIndex - 1].classList.remove('bg-green-600');
      dots[slideIndex - 1].classList.add('bg-yellow-500');*/
    }
  </script>
<?php else : ?>
  <!-- Image gallery -->
  <div class="wpb-gallery-container classic">
    <h2 class="sr-only">
      <?php _e( 'Images', 'wpboutik' ); ?>
    </h2>
    <!-- Image selector -->
    <div class="image-gallery-single" aria-orientation="horizontal" role="tablist">
      <?php
      $images = get_post_meta( get_the_ID(), 'galerie_images', true );
      if ( has_post_thumbnail() ) :
        if ( $images ) : ?>
          <button data-tab="1" class="showimggalery relative flex h-24 cursor-pointer items-center justify-center rounded-md bg-white text-sm font-medium uppercase text-gray-900 hover:bg-gray-50 focus:outline-none focus:ring focus:ring-opacity-50" aria-controls="tabs-2-panel-1" role="tab" type="button">
            <span class="sr-only"> 
              Angled view 
            </span>
            <span class="absolute inset-0 overflow-hidden rounded-md">
              <?php the_post_thumbnail( 'post-thumbnail', array(
              'class' => 'h-full w-full object-contain',
              'alt'   => esc_html( get_the_title() )
              ) ); ?>
            </span>
            <span class="ring-indigo-500 pointer-events-none absolute inset-0 rounded-md ring-2 ring-offset-2" aria-hidden="true"></span>
          </button>
        <?php
        endif;
      endif;

      if ( $images ) :
      $images = explode( ',', $images );
      $i  = 2;
        foreach ( $images as $img ) : ?>
          <button data-tab="<?php echo $i; ?>"
          class="showimggalery relative flex h-24 cursor-pointer items-center justify-center rounded-md bg-white text-sm font-medium uppercase text-gray-900 hover:bg-gray-50 focus:outline-none focus:ring focus:ring-opacity-50"
          aria-controls="tabs-2-panel-1" role="tab"
          type="button">
            <span class="sr-only"> Angled view </span>
            <span class="absolute inset-0 overflow-hidden rounded-md">
              <?php echo wp_get_attachment_image( $img, 'post-thumbnail', false, array(
              'class' => 'h-full w-full object-contain',
              'alt'   => esc_html( get_the_title() . '-' . $i )
              ) ); ?>
            </span>
            <span class="ring-transparent pointer-events-none absolute inset-0 rounded-md ring-2 ring-offset-2" aria-hidden="true"></span>
          </button>
          <?php
          $i ++;
        endforeach;
      endif; ?>
    </div>
    <!-- Image selector End -->

    <div>
    <!-- Tab panel, show/hide based on tab state. -->
      <?php
      if ( has_post_thumbnail() ) : ?>
        <div id="tabs-2-panel-1"
        aria-labelledby="tabs-2-tab-1" role="tabpanel" tabindex="0"
        class="imggalfirst">
          <?php the_post_thumbnail( 'large', array(
          'class' => 'max-h-[592px] h-full w-full object-contain sm:rounded-lg',
          'alt'   => esc_html( get_the_title() )
          ) ); ?>
        </div>
      <?php
      else : ?>
        <div id="tabs-2-panel-0" aria-labelledby="tabs-2-tab-1"
        role="tabpanel"
        tabindex="0" class="imggalfirst">
          <?php echo wpb_get_default_image( "max-h-[592px] h-full w-full object-contain sm:rounded-lg" ); ?>
        </div>
      <?php endif;
      if ( $images ) :
        $i = 2;
        foreach ( $images as $img ) : ?>
          <div id="tabs-2-panel-<?php echo $i; ?>" aria-labelledby="tabs-2-tab-1" role="tabpanel" tabindex="0" class="imggal hidden">
          <!-- min-h-[592px] -->
            <?php echo wp_get_attachment_image( $img, 'large', false, array(
            'class' => 'max-h-[592px] h-full w-full object-contain sm:rounded-lg',
            'alt'   => esc_html( get_the_title() . '-' . $i )
            ) ); ?>
          </div>
          <?php
          $i ++;
        endforeach;
      endif; ?>
    </div>
    <script>
      jQuery('.showimggalery').on('click', function () {
        jQuery('.showimggalery').find('span:last').removeClass('ring-indigo-500');
        jQuery('.showimggalery').find('span:last').addClass('ring-transparent');
        jQuery(this).find('span:last').addClass('ring-indigo-500');
        jQuery(this).find('span:last').removeClass('ring-transparent');

        jQuery('.imggalfirst').parent().addClass('hidden');
        jQuery('.imggal').parent().addClass('hidden');
        jQuery('#tabs-2-panel-' + jQuery(this).data('tab')).parent().removeClass('hidden');

        jQuery('.imggalfirst').addClass('hidden');
        jQuery('.imggal').addClass('hidden');

        jQuery('#tabs-2-panel-' + jQuery(this).data('tab')).removeClass('hidden');
        jQuery('#tabs-2-panel-' + jQuery(this).data('tab')).addClass('block');
      });
    </script>
  </div>

<?php endif; ?>