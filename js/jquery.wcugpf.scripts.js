jQuery(document).ready(function($) {
    active_feed = $('#wc_ugpf_tab_active input');
    ugpf_items = $('#wc_ugpf_items');
    ugpf_tab_unique = $('#wc_ugpf_tab_unique input:checkbox');
    ugpf_tab_unique_wrap = $('#wc_ugpf_tab_unique .wc_ugp_wrap');
    ugpf_tab_tax = $('#wc_ugpf_tab_tax input:checkbox');
    ugpf_tab_tax_wrap = $('#wc_ugpf_tab_tax .wc_ugp_wrap');
    ugpf_tab_apparel = $('#wc_ugpf_tab_apparel input:checkbox');
    ugpf_tab_apparel_wrap = $('#wc_ugpf_tab_apparel .wc_ugp_wrap');
    ugpf_tab_installments = $('#wc_ugpf_tab_installments input:checkbox');
    ugpf_tab_installments_wrap = $('#wc_ugpf_tab_installments .wc_ugp_wrap');

    function ugpf_is_activated(id, target) {
       if ( id.is(":checked") ) {
            target.css('display', 'block');
        } else {
            target.css('display', 'none');
        }
    }

    // Test on page load.
    ugpf_is_activated(active_feed, ugpf_items);
    ugpf_is_activated(ugpf_tab_unique, ugpf_tab_unique_wrap);
    ugpf_is_activated(ugpf_tab_tax, ugpf_tab_tax_wrap);
    ugpf_is_activated(ugpf_tab_apparel, ugpf_tab_apparel_wrap);
    ugpf_is_activated(ugpf_tab_installments, ugpf_tab_installments_wrap);

    // Test on change.
    active_feed.on('change', function() {
        ugpf_is_activated(active_feed, ugpf_items);
    });

    ugpf_tab_unique.on('change', function() {
        ugpf_is_activated(ugpf_tab_unique, ugpf_tab_unique_wrap);
    });

    ugpf_tab_tax.on('change', function() {
        ugpf_is_activated(ugpf_tab_tax, ugpf_tab_tax_wrap);
    });

    ugpf_tab_apparel.on('change', function() {
        ugpf_is_activated(ugpf_tab_apparel, ugpf_tab_apparel_wrap);
    });

    ugpf_tab_installments.on('change', function() {
        ugpf_is_activated(ugpf_tab_installments, ugpf_tab_installments_wrap);
    });
});
