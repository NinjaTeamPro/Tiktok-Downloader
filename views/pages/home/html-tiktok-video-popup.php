<?php
defined('ABSPATH') || exit;
?>
<div id="njt-tk-dialog">
   <div class="njt-tk-popup-video">
      <div class="njt-tk-video-container">
        <div class="njt-tk-item-image">
          <video controls class="njt-video">
            <source src="<?php echo (!empty($dataPopup['videoUrl']) ? esc_html($dataPopup['videoUrl']) : '') ?>" type="video/mp4">
          </video>
        </div>
      </div>
      <div class="njt-tk-content-container njt-flex-content">
        <div class="njt-tk-popup-title">
          <h3 class="njt-tk-text-title"><?php _e("Your video is ready to download", NJT_TK_DOMAIN);?></h3>
        </div>
        <div class="njt-video-content">
          <div class="njt-tk-user-infor">
            <div class="njt-tk-user-des">
              <div class="njt-tk-avatar">
                <div class="img-avatar">
                  <img src="<?php echo (!empty($dataPopup['avatar']) ? esc_html($dataPopup['avatar']) : '') ?>">
                </div>
              </div>
              <div class="njt-tk-user-name">
                <h2 class="user-name"><?php echo (!empty($dataPopup['username']) ? esc_html($dataPopup['username']) : '') ?></h2>
                <h2 class="user-nickname"><?php echo ((!empty($dataPopup['nickName']) ? esc_html($dataPopup['nickName']) : '') . ' - ' . (!empty($dataPopup['createTime']) ? esc_html($dataPopup['createTime']) : '')); ?></h2>
              </div>
            </div>
            <div class="njt-tk-follow">
              <a type="button" class="button njt-btton-follow" href="<?php echo (!empty($dataPopup['username']) ? 'https://www.tiktok.com/@' . esc_html($dataPopup['username']) : 'https://www.tiktok.com/') ?>" target="_blank">
              <?php _e("View Tiktok", NJT_TK_DOMAIN);?>
              </a>
            </div>
          </div>
          <div class="njt-tk-user-detail">
            <span><?php echo (!empty($dataPopup['videoDes']) ? esc_html($dataPopup['videoDes']) : '') ?></span>
          </div>
          <div class="njt-tk-music-infor">
              <h2 class="music-info">
                <a href="<?php echo (!empty($dataPopup['musicName'] && $dataPopup['musicId']) ? 'https://www.tiktok.com/music/' . $dataPopup['musicName']. '-'. $dataPopup['musicId'] : 'javascript:void(0)') ?>" class="" target="_blank"><?php echo (!empty($dataPopup['musicName']) ? esc_html($dataPopup['musicName']): ''); echo(!empty($dataPopup['authorName']) ? '-'.esc_html($dataPopup['authorName']): '')?></a>
              </h2>
          </div>
          <div class="njt-tk-user-action">
            <span class="njt-plays"><?php echo (!empty($dataPopup['playCount']) ? esc_html($dataPopup['playCount']) : '0') ?> <?php _e("plays", NJT_TK_DOMAIN);?></span> -
            <span class="njt-likes"><?php echo (!empty($dataPopup['videoLike']) ? esc_html($dataPopup['videoLike']) : '0') ?> <?php _e("likes", NJT_TK_DOMAIN);?></span> -
            <span class="njt-comments"><?php echo (!empty($dataPopup['videoComment']) ? $dataPopup['videoComment'] : '0') ?> <?php _e("comments", NJT_TK_DOMAIN);?></span>
          </div>
          <div class="njt-tk-video-download">
            <form action="<?php echo (admin_url('admin-ajax.php').'?action=njt_tk_download_video') ?>" method="post">
              <input type='hidden' name='njt-tk-settings-security-token' value='<?php echo wp_create_nonce('njt-tk-settings-security-token'); ?>'>

              <input type='hidden' name='njt-tk-download-video' value='<?php echo esc_html($dataPopup['videoUrl'])?>'>

              <button type="submit" class="button njt-button-download-no-watermark" name="njt-button-download-no-watermark" value="njt-button-download-no-watermark">
                <?php _e("Download without Watermark", NJT_TK_DOMAIN);?>
              </button>
              <button type="submit" class="button njt-button-download-watermark" name="njt-button-download-watermark" value="njt-button-download-watermark">
                <img src="<?php echo (!empty($dataPopup['pluginName']) ? '../wp-content/plugins/' . esc_html($dataPopup['pluginName']) . '/assets/home/img/download-video.svg' : '') ?>">
              </button>
              <button type="submit" class="button njt-button-download-music" name="njt-button-download-music" value="njt-button-download-music">
                <img src="<?php echo (!empty($dataPopup['pluginName']) ? '../wp-content/plugins/' . esc_html($dataPopup['pluginName']) . '/assets/home/img/download-music.svg' : '') ?>">
              </button>
            </form>
          </div>
       </div>
      </div>
   </div>
</div>
