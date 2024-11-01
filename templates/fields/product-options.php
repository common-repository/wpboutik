<?php
  $options = get_post_meta( get_the_ID(), 'options', true );
  if ( $options )  :
    $options_decoded = json_decode( $options );
    foreach ( $options_decoded as $option ) :
      $class_select = '';
      if ( empty( $option->values ) ) {
        continue;
      }
      $option_name_lower = strtolower( $option->name );
      switch ($option->id) {
          case 'opt_visuel_gc' :
            if (sizeof($option->values) < 2) {
                $class_select .= 'hidden';
            }
        
              ob_start();
                  ?> <div class="wpb-opt-visuel"><?php
                  $i = 0;
                  foreach ($option->values as $opt) :?>
                  <div>
                      <input <?= ($i == 0) ? 'checked ' : '' ?>type="radio" id="opt_visuel_gc_<?= $opt->id; ?>" name="<?= 'select_' . $option_name_lower ?>" value="<?= $opt->id; ?>" class="select_option_wpb hidden peer wpb-radio wpb-radio-visuel" required />
                      <label for="opt_visuel_gc_<?= $opt->id; ?>">
                          <img class="opt_visuel_gc_images" src="<?= $opt->value ?>">
                      </label>
                  </div>
                  
              <?php $i++;
              endforeach;
              ?> </div> <?php
              $field = ob_get_clean();
          break;
          case 'opt_price_gc' :
              ob_start();
              ?> <div class="wpb-opt-price"> <?php
                  $i = 0;
                  foreach ($option->values as $opt) :?>
                  <div>
                      <input <?= ($i == 0) ? 'checked ' : '' ?>type="radio" id="opt_price_gc_<?= $opt->id; ?>" name="<?= 'select_' . $option_name_lower ?>" value="<?= $opt->id; ?>" class="select_option_wpb hidden peer wpb-radio wpb-radio-price" required />
                      <label for="opt_price_gc_<?= $opt->id; ?>">
                        <?php if($opt->id == 'custom') : ?>                        
                            <?= __('Custom price', 'wpboutik') ?>
                        <?php else : ?>                        
                            <?= $opt->value ?> <?= get_wpboutik_currency_symbol() ?>
                        <?php endif; ?>                        
                      </label>
                  </div>
              <?php $i++; endforeach;
              ?> </div> <?php
              $field = ob_get_clean();
          break;
          default :
              $selected = '';
              if ( count( $option->values ) === 1 ) {
                  $selected     = 'selected';
                  $class_select = 'hidden';
              } 

              ob_start(); ?>
                    <?php if ($option->type == 'text') : ?>
                        <select name="<?php echo 'select_' . $option_name_lower; ?>"
                                id="<?php echo $option->id; ?>"
                                class="select_option_wpb">
                            <option value="">Choisir une option</option>
                            <?php
                            foreach ( $option->values as $value ) : ?>
                                <option
                                        value="<?php echo $value->id; ?>" <?php echo $selected; ?>><?php echo $value->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else : ?>
                        <div class="wpb-opt-price">
                            <input type="radio" value="" checked name="<?php echo 'select_' . $option_name_lower ?>" id="<?php echo 'select_' . $option_name_lower; ?>" class="select_option_wpb hidden peer wpb-radio">
                            <?php foreach ( $option->values as $value ) : ?>
                                <input type="radio" value="<?= $value->id ?>"<?= !empty($selected) ? ' checked' : ''?> name="<?php echo 'select_' . $option_name_lower ?>" id="<?php echo 'select_' . $option_name_lower.'_'.$value->id; ?>" class="select_option_wpb hidden peer wpb-radio">
                                
                                <label for="<?php echo 'select_' . $option_name_lower.'_'.$value->id; ?>"<?php if($option->type == 'color') { echo ' style="background-color: '.$value->name.'; width: 2.5em; height: 2.5em;"'; } ?>>
                                    <?php if($option->type == 'radio') : ?>                        
                                        <?= $value->name ?>
                                    <?php else : ?>
                                        <span class="sr-only"><?= $value->name ?></span>
                                    <?php endif; ?>                        
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
              <?php $field = ob_get_clean();
          break;
      }
      ?>
      <div class="wpb-field <?= $class_select; ?>">
        <p><?php echo $option->name; ?></p>
        <label for="<?= $option->id; ?>" class="sr-only">
            Choose a <?= $option->name; ?>
        </label>
        <?= $field ?>
      </div>
      <?php
      //endif;
    endforeach;
  endif;
