jQuery(function($) {
    var whl_domains = [
        'http://\\w\\.hiphotos\\.baidu\\.com'
    ];
    var source_url = (window.wp_site_url || '/')
        + '/wp-admin/admin-ajax.php?action=hotlink_img&url=';

    //var i, domain;
    //for(i = 0; i < whl_domains.length; ++i) {
    //    domain = whl_domains[i];
    //    $('img[src^="'+domain+'"]').each(function() {
    //        $(this).attr('src', source_url + $(this).attr('src'));
    //    });
    //}
    $('img').each(function() {
        var url = $(this).attr('src') || '';
        var target = source_url + url;
        $.each(whl_domains, function(index, domain) {
            if((new RegExp(domain)).test(url)) url = '~~~';
        });
        if(url === '~~~') $(this).attr('src', target);
    });
});