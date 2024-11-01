<form class="space-y-6" action="#" method="POST">
  <div>
    <label for="email" class="block text-sm font-medium text-gray-700"><?php _e( 'Username or Email Address' ); ?></label>
    <div class="mt-1">
      <input id="user_login" name="log" type="text" required class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-[var(--backgroundcolor)] focus:outline-none focus:ring-[var(--backgroundcolor)] sm:text-sm" style="--backgroundcolor: <?php echo $backgroundcolor; ?>">
    </div>
  </div>

  <div>
    <label for="password" class="block text-sm font-medium text-gray-700"><?php _e( 'Password' ); ?></label>
    <div class="mt-1">
      <input id="user_pass" name="pwd" type="password" autocomplete="current-password" required class="block w-full appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-[var(--backgroundcolor)] focus:outline-none focus:ring-[var(--backgroundcolor)] sm:text-sm" style="--backgroundcolor: <?php echo $backgroundcolor; ?>">
    </div>
  </div>

  <div class="flex items-center justify-between">
    <div class="flex items-center">
      <input id="rememberme" name="rememberme" type="checkbox" class="m-0 h-4 w-4 rounded border-gray-300 text-[var(--backgroundcolor)] focus:ring-[var(--backgroundcolor)]" style="--backgroundcolor: <?php echo $backgroundcolor; ?>">
      <label for="rememberme" class="m-0 ml-2 block text-sm text-gray-900"><?php esc_html_e( 'Remember Me' ); ?></label>
    </div>


    <div class="text-sm">
      <a href="<?php echo esc_url( get_permalink( wpboutik_get_page_id( 'account' ) ).get_option( 'wpboutik_myaccount_lost_password_endpoint', 'lost-password' ) ); ?>" class="wpb-link"><?php _e( 'Lost your password?' ); ?></a>
    </div>
  </div>

  <div>
    <?php wp_nonce_field( 'wpboutik-login', 'wpboutik-login-nonce' ); ?>
    <button type="submit" class="wpb-btn" >
      <?php _e( 'Sign in', 'wpboutik' ); ?>
    </button>
  </div>
</form>