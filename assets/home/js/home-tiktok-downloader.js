const njtTiktokDownloader = {
  ajaxTiktokSearch() {
    jQuery("#njt-tk-button-search").click(() => {
      const valueSearch = jQuery("#njt-tk-search").val();
      const dataSearch = {
        'action': 'njt_tk_tiktok_search',
        'valueSearch': valueSearch.trim(),
        'nonce': wpData.nonce,
      }
      const domItemContent = jQuery('.njt-tk-content-hidden').html()
      jQuery(".pagination").css('display', 'none')
      jQuery.post(
        wpData.admin_ajax,
        dataSearch,
        (res) => {
          const dataRes = res.data && res.data.dataVideo ? res.data.dataVideo : []
          const searchType = res.data && res.data.searchType ? res.data.searchType : ''
          jQuery(".njt-tk-main-layout-content").empty()
          jQuery('.njt-tk-search-results').hide()
          if (searchType == 2) {
            if (dataRes.videoId) {
              const dataVideo = {
                'action': 'njt_tk_search_videourl',
                'nonce': wpData.nonce,
                'dataSearch': dataRes
              }
              jQuery.post(
                wpData.admin_ajax,
                dataVideo,
                (data) => {
                  jQuery(".njt-tk-main-layout-content").append(jQuery(data))
                  njtTiktokDownloader.responsiveCss();
                }
              )
            } else {
              jQuery('.njt-tk-search-results').show()
              jQuery('.njt-tk-search-results .search-results-num').text('(0)')
            }
          } else {
            if (dataRes.length > 0) {
              for (const element of dataRes.slice(0, 9)) {
                let imgUrl = element.covers
                let objDomItemContent = jQuery(domItemContent)
                objDomItemContent.attr('data-fancybox-url', JSON.stringify(element))
                jQuery(objDomItemContent[0].childNodes[1].childNodes[1]).css("background-image", "url(" + imgUrl + ")")
                jQuery(objDomItemContent[0].childNodes[1].childNodes[1].childNodes[1].childNodes[3]).text(element.playCount)
                jQuery(".njt-tk-main-layout-content").append(jQuery(objDomItemContent)[0])
              }

              let munberPage = 1
              jQuery(".pagination-list").empty()
              for (let i = 0; i < dataRes.length; i += 9) {
                let from = i
                let to = i + 9
                if (munberPage < 4) {
                  jQuery(".pagination-list").append(`<a href="javascript:void(0)" class="paginnation-curent" data-from="${from}" data-to="${to}">${munberPage++}</a>`)
                } else {
                  jQuery(".pagination-list").append(`<a href="javascript:void(0)" class="paginnation-curent" style="display:none" data-from="${from}" data-to="${to}">${munberPage++}</a>`)
                }

              }
              jQuery(".pagination").css('display', 'flex')
              jQuery(".paginnation-curent").first().addClass('active')

              //display search results
              jQuery('.njt-tk-search-results').show()
              jQuery('.njt-tk-search-results .search-results-num').text(`(${dataRes.length})`)
              //Run fancybox
              njtTiktokDownloader.libFancybox();
              //pagination
              njtTiktokDownloader.pagination(dataRes);
              //set width for content
              jQuery(window).resize(function () {
                if (jQuery('.njt-tk-main-layout-content').width() <= 610) {
                  jQuery('.njt-tk-main-layout-content').addClass('njt-tk-content-halfwidth')
                } else {
                  jQuery('.njt-tk-main-layout-content').removeClass('njt-tk-content-halfwidth')
                }
              })
            } else {
              jQuery('.njt-tk-search-results').show()
              jQuery('.njt-tk-search-results .search-results-num').text(`(${dataRes.length})`)
            }
          }
        }
      );
    })
  },
  formatNumber(n) {
    if (n < 1e3) return n;
    if (n >= 1e3 && n < 1e6) return +(n / 1e3).toFixed(1) + "K";
    if (n >= 1e6 && n < 1e9) return +(n / 1e6).toFixed(1) + "M";
    if (n >= 1e9 && n < 1e12) return +(n / 1e9).toFixed(1) + "B";
    if (n >= 1e12) return +(n / 1e12).toFixed(1) + "T";
  },
  libFancybox() {
    jQuery('.fancybox-thumb').fancybox({
      type: 'ajax',
      maxWidth: 1000,
      maxHeight: 500,
      fitToView: false,
      autoSize: true,
      closeClick: false,
      openEffect: 'none',
      closeEffect: 'none',
      centerOnScroll: true,
      overlayColor: "#000",
      overlayOpacity: 0.9,
      ajax: {
        settings: {
          type: 'POST',
          data: {
            action: 'njt_tk_view_popup',
            nonce: wpData.nonce,
            fancybox: true,
            datavideo: function () {
              const slideCurrent = jQuery.fancybox.getInstance().current
              return jQuery(slideCurrent)[0].opts.$orig.context.dataset.fancyboxUrl;
            }
          }
        }
      },
      beforeShow: function () {
        const slideCurrent = jQuery.fancybox.getInstance().current
        if (!jQuery(slideCurrent)[0].opts.$orig.context.dataset.fancyboxUrl) {
          jQuery.fancybox.close();
        }
      },
      afterLoad: function () {
        const textDes = jQuery('.njt-tk-user-detail span').text()
        const makeArDes = textDes.split(" ")
        const newArDes = makeArDes.map(function (item) {
          if (item.indexOf("#") > -1) {
            let link = item.replace('#', '')
            return `<a class="njt-tk-link" target="_blank" href="https://www.tiktok.com/tag/${link}"> ${item} </a>`
          } else if (item.indexOf("@") > -1) {
            return `<a class="njt-tk-link" target="_blank" href="https://www.tiktok.com/${item}"> ${item} </a>`
          } else {
            return item
          }
        });
        jQuery(".njt-tk-user-detail span").html(newArDes.join(" "))
      }
    });
  },
  pagination(dataRes) {
    jQuery(".paginnation-curent").on("click", function (e) {
      const dataFrom = jQuery(this).data('from')
      const dataTo = jQuery(this).data('to')
      jQuery('.paginnation-curent').hide()
      jQuery(this).show()
      jQuery(this).prev().show()
      jQuery(this).prev().prev().show()
      jQuery(this).next().show()
      jQuery(this).next().next().show()
      const domItemContentPagination = jQuery('.njt-tk-content-hidden').html()

      jQuery('.paginnation-curent').removeClass('active')
      jQuery(this).addClass('active')
      jQuery('.njt-tk-main-layout-content').empty()

      console.log(dataRes.slice(dataFrom, dataTo))
      for (const element of dataRes.slice(dataFrom, dataTo)) {
        let imgUrl = element.covers
        let objDomItemContent = jQuery(domItemContentPagination)
        objDomItemContent.attr('data-fancybox-url', JSON.stringify(element))
        jQuery(objDomItemContent[0].childNodes[1].childNodes[1]).css("background-image", "url(" + imgUrl + ")")
        jQuery(objDomItemContent[0].childNodes[1].childNodes[1].childNodes[1].childNodes[3]).text(element.playCount)
        jQuery(".njt-tk-main-layout-content").append(jQuery(objDomItemContent)[0])
      }

      njtTiktokDownloader.SmoothScrollTo(".njt-tk-downloader", 1000);
      njtTiktokDownloader.libFancybox();
    })
  },
  eventPrevOrNextPagination() {
    jQuery(".pagination-prev").on("click", function () {
      jQuery('.paginnation-curent.active').prev().click()

    })
    jQuery(".pagination-next").on("click", function () {
      jQuery('.paginnation-curent.active').next().click()
    })
  },
  SmoothScrollTo(idOrName, timelength) {
    var timelength = timelength || 1000;
    jQuery('html, body').animate({
      scrollTop: jQuery(idOrName).offset().top - 70
    }, timelength, function () {
      window.location.hash = idOrName;
    });
  },
  responsiveCss() {
    jQuery(window).resize(function () {
      if (jQuery('.njt-tk-popup-video').width() <= 610) {
        jQuery('.njt-tk-popup-video').addClass('njt-style-full-width')
      } else {
        jQuery('.njt-tk-popup-video').removeClass('njt-style-full-width')
      }
    })
  }
}

jQuery(document).ready(() => {
  njtTiktokDownloader.ajaxTiktokSearch();
  njtTiktokDownloader.eventPrevOrNextPagination();
})