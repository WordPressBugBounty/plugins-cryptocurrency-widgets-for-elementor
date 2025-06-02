<?php

/**
 * 
 * Addon dashboard sidebar.
 */

 if( !isset($this->main_menu_slug) ):
    return false;
 endif;

 $cool_support_email = "https://coolplugins.net/support/?utm_source=ccew_plugin&utm_medium=inside&utm_campaign=support&utm_content=dashboard";
 $hire_devloper = "https://coolplugins.net/support/?utm_source=ccew_plugin&utm_medium=inside&utm_campaign=support&utm_content=dashboard";
?>

 <div class="cool-body-right">
    <a href="https://coolplugins.net/?utm_source=ccew_plugin&utm_medium=inside&utm_campaign=author_page&utm_content=dashboard_pro" target="_blank"><img src="<?php echo esc_url(plugin_dir_url( $this->addon_file ) .'/assets/coolplugins-logo.png'); ?>"></a>

    <ul>
      <li>Cool Plugins develops best crypto plugins for WordPress.</li>
      <li>Our crypto plugins have <b>10000+</b> active installs.</li>
      <li>For any query or support, please contact plugin support team.
      <br><br>
      <a href="<?php echo esc_url($cool_support_email); ?>" target="_blank" class="button button-secondary">Premium Plugin Support</a>
      <br><br>
      </li>
      <li>We also provide <b>crypto plugins customization</b> services.
      <br><br>
      <a href="<?php echo esc_url( $hire_devloper ); ?>" target="_blank" class="button button-primary">Hire Developer</a>
      <br><br>
      </li>
   </ul>
    </div>  

</div><!-- End of main container-->