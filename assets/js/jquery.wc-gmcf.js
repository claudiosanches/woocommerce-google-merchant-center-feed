/**
 * Metabox scripts.
 */
jQuery(document).ready(function($) {
    var active_feed = $('#wc_gmcf_tab_active input'),
        gmcf_items = $('#wc_gmcf_items'),
        gmcf_tab_unique = $('#wc_gmcf_tab_unique input:checkbox'),
        gmcf_tab_unique_wrap = $('#wc_gmcf_tab_unique .wc_ugp_wrap'),
        gmcf_tab_tax = $('#wc_gmcf_tab_tax input:checkbox'),
        gmcf_tab_tax_wrap = $('#wc_gmcf_tab_tax .wc_ugp_wrap'),
        gmcf_tab_apparel = $('#wc_gmcf_tab_apparel input:checkbox'),
        gmcf_tab_apparel_wrap = $('#wc_gmcf_tab_apparel .wc_ugp_wrap'),
        gmcf_tab_installments = $('#wc_gmcf_tab_installments input:checkbox'),
        gmcf_tab_installments_wrap = $('#wc_gmcf_tab_installments .wc_ugp_wrap');

    function gmcf_is_activated(id, target) {
       if ( id.is(":checked") ) {
            target.css('display', 'block');
        } else {
            target.css('display', 'none');
        }
    }

    // Test on page load.
    gmcf_is_activated(active_feed, gmcf_items);
    gmcf_is_activated(gmcf_tab_unique, gmcf_tab_unique_wrap);
    gmcf_is_activated(gmcf_tab_tax, gmcf_tab_tax_wrap);
    gmcf_is_activated(gmcf_tab_apparel, gmcf_tab_apparel_wrap);
    gmcf_is_activated(gmcf_tab_installments, gmcf_tab_installments_wrap);

    // Test on change.
    active_feed.on('change', function() {
        gmcf_is_activated(active_feed, gmcf_items);
    });

    gmcf_tab_unique.on('change', function() {
        gmcf_is_activated(gmcf_tab_unique, gmcf_tab_unique_wrap);
    });

    gmcf_tab_tax.on('change', function() {
        gmcf_is_activated(gmcf_tab_tax, gmcf_tab_tax_wrap);
    });

    gmcf_tab_apparel.on('change', function() {
        gmcf_is_activated(gmcf_tab_apparel, gmcf_tab_apparel_wrap);
    });

    gmcf_tab_installments.on('change', function() {
        gmcf_is_activated(gmcf_tab_installments, gmcf_tab_installments_wrap);
    });
});
