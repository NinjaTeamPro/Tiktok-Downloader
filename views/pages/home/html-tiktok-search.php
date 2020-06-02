<?php
defined('ABSPATH') || exit;
?>

<div class="njt-tk-downloader">
  <div>
    <div class="wrap njt-tk-text-center">
        <h2 class="text-heading"><?php echo (!empty($this->titokSetting['text_heading']) ? esc_html($this->titokSetting['text_heading']) : '') ?></h2>
        <p><?php echo (!empty($this->titokSetting['text_description']) ? esc_html($this->titokSetting['text_description']) : '') ?></p>
    </div>
    <div>
      <div>
          <div class="njt-tk-input-search">
            <input type="search" id="njt-tk-search" name="njt-tk-search" placeholder="<?php _e("Enter @username, #hashtag or video url", NJT_TK_DOMAIN);?>">
            <button type="button" class="button button-primary njt-tk-button-search" id="njt-tk-button-search"> <?php _e("Search", NJT_TK_DOMAIN);?></button>
          </div>
        <p><?php _e("Example:", NJT_TK_DOMAIN);?> <?php echo (!empty($this->titokSetting['text_example']) ? esc_html($this->titokSetting['text_example']) : '') ?></p>
      </div>
      <div class="njt-tk-main-layout">
        <div class="njt-tk-search-results" style="display:none">
            <span><?php _e("Search results", NJT_TK_DOMAIN);?></span>
            <span class="search-results-num"></span>
        </div>
        <div class="njt-tk-main-layout-content">
          <!-- Display content after search -->
        </div>
        <div class="pagination" style="display:none">
          <a href="javascript:void(0)" class="pagination-prev">&laquo;</a>
          <div class="pagination-list">
          </div>
          <a href="javascript:void(0)" class="pagination-next">&raquo;</a>
        </div>
        <div class="njt-tk-content-hidden" style="display:none">
            <div class="njt-tk-content-item fancybox-thumb" data-fancybox="video-popup" href="<?php echo (admin_url('admin-ajax.php')) ?>">
              <div class="njt-tk-item-video">
                <div class="njt-tk-item-image image-card">
                  <div class="video-bottom-infor">
                    <img src="<?php echo (NJT_TK_PLUGIN_URL . '/assets/home/img/multimedia.svg') ?>" class="video-like-icon">
                    <strong class="video-count">0M</strong>
                  </div>
                </div>
              </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
