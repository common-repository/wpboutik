<?php do_action( 'wpboutik_before_lost_password_form' ); ?>
  <form method="post" class="space-y-6 lost_reset_password">

  <p><?php echo apply_filters( 'wpboutik_lost_password_message', esc_html__( 'Please enter your username or email address. You will receive an email message with instructions on how to reset your password.' ) ); ?></p>

  <p>
      <label for="user_login"><?php esc_html_e( 'Username or Email Address' ); ?></label>
      <div class="mt-1">
          <input required class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-[var(--backgroundcolor)] focus:outline-none focus:ring-[var(--backgroundcolor)] sm:text-sm" style="--backgroundcolor: <?php echo $backgroundcolor; ?>"
          type="text" name="user_login" id="user_login"
          autocomplete="username"/>
      </div>
  </p>

  <div class="clear"></div>

  <?php do_action( 'wpboutik_lostpassword_form' ); ?>

  <p class="woocommerce-form-row form-row">
    <input type="hidden" name="wpb_reset_password" value="true"/>
    <button type="submit"
            class="wpb-btn"
            value="<?php esc_attr_e( 'Reset password', 'wpboutik' ); ?>"><?php esc_html_e( 'Reset password', 'wpboutik' ); ?></button>
  </p>

  <?php wp_nonce_field( 'lost_password', 'wpboutik-lost-password-nonce' ); ?>

  </form>
<?php do_action( 'wpboutik_after_lost_password_form' ); ?>