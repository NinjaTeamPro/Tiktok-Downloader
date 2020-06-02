<?php
defined('ABSPATH') || exit;
if (isset($_POST) && !empty($_POST) && !empty($_POST['njt-tk-settings-form-submit'])) {
    if (!wp_verify_nonce($_POST['njt-tk-settings-security-token'], 'njt-tk-settings-security-token')) {
        wp_die();
    }
    if(filter_var($_POST['njt-tk-text-shortcode'], FILTER_SANITIZE_STRING)) {
      $textShortcode = $_POST['njt-tk-text-shortcode'];
      $textShortcode = str_replace("[", "", $textShortcode);
      $textShortcode = str_replace("]", "", $textShortcode);
      $this->titokSetting['text_shortcode'] = sanitize_text_field($textShortcode);
    } else {
      $this->titokSetting['text_shortcode'] = '';
    }
    $this->titokSetting['text_heading'] = filter_var($_POST['njt-tk-text-heading'], FILTER_SANITIZE_STRING) ? sanitize_text_field($_POST['njt-tk-text-heading']) : '';
    $this->titokSetting['text_description'] = filter_var($_POST['njt-tk-text-description'], FILTER_SANITIZE_STRING) ? sanitize_text_field($_POST['njt-tk-text-description']) : '';
    $this->titokSetting['text_example'] = filter_var($_POST['njt-tk-text-example'], FILTER_SANITIZE_STRING) ? sanitize_text_field($_POST['njt-tk-text-example']) : '';
}
?>

<div class="njt-tk-admin-setting">
  <div class="njt-tk-admin-content">
    <div class="wrap">
      <h2><?php _e("Settings", NJT_TK_DOMAIN);?></h2>
    </div>
    <form action="" class="njt-tk-plugin-setting settings-form" method="POST">
       <!-- creat token -->
       <input type='hidden' name='njt-tk-settings-security-token'
        value='<?php echo wp_create_nonce('njt-tk-settings-security-token'); ?>'>
      <table class="form-table">
        <tr>
          <th><?php _e("Shortcode", NJT_TK_DOMAIN);?></th>
          <td>
              <input name="njt-tk-text-shortcode" id="njt-tk-text-shortcode" type="text" value="<?php echo (!empty($this->titokSetting['text_shortcode']) ? esc_attr('['.$this->titokSetting['text_shortcode'].']') : '') ?>">
          </td>
        </tr>
        <tr>
          <th><?php _e("Text heading", NJT_TK_DOMAIN);?></th>
          <td>
              <textarea name="njt-tk-text-heading" id="njt-tk-text-heading" class="njt-tk-settting-width"><?php echo (!empty($this->titokSetting['text_heading']) ? esc_textarea($this->titokSetting['text_heading']) : '') ?></textarea>
          </td>
        </tr>
        <tr>
          <th><?php _e("Text description", NJT_TK_DOMAIN);?></th>
          <td>
              <textarea name="njt-tk-text-description" id="njt-tk-text-description" class="njt-tk-settting-width"><?php echo (!empty($this->titokSetting['text_description']) ? esc_textarea($this->titokSetting['text_description']) : '') ?></textarea>
          </td>
        </tr>
        <tr>
          <th><?php _e("Text example", NJT_TK_DOMAIN);?></th>
          <td>
              <textarea name="njt-tk-text-example" id="njt-tk-text-example" class="njt-tk-settting-width"><?php echo (!empty($this->titokSetting['text_example']) ? esc_textarea($this->titokSetting['text_example']) : '') ?></textarea>
          </td>
        </tr>
         <!-- button submit -->
         <tr>
          <td></td>
          <td>
            <p class="submit">
              <input type="submit" name="njt-tk-settings-form-submit" id="njt-tk-settings-form-submit"
                class="button button-primary njt-tk-settings-form-submit" value="<?php echo (esc_attr('Save changes')) ?>">
            </p>
          </td>
        </tr>
      </table>
    </form>
  </div>
</div>
