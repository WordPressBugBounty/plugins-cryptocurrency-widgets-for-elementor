<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
?>
<#
// Validate and sanitize input settings
var design_type = settings.ccewd_widget_type || 'tabular',
    all_coin_wall_add = settings.ccewd_repeater_data || [],
    title = _.escape(settings.ccewd_coins_title || 'Donate [coin-name] to this address'),
    description = _.escape(settings.ccewd_coins_description || 'Scan the QR code or copy the address below into your wallet to send some [coin-name]'),
    metamask_description = _.escape(settings.ccewd_metamask_title || 'Donate ETH Via PAY With Metamask'),
    cdb_metamask_title = _.escape(settings.ccewd_metamask_description || 'Donate With MetaMask'),
    metamask_price = _.escape(settings.ccewd_metamask_price || '0.005'),
    i = 0,
    coin_links = [],
    coin_tabs = [],
    classic_list = [],
    output = [],
    random = Math.floor((Math.random() * 10000) + 1);

// Validate wallet address format
function isValidWalletAddress(address, type) {
    if (!address) return false;
    
    // Basic validation for different coin types
    if (type === 'metamask') {
        return /^0x[a-fA-F0-9]{40}$/.test(address); // Ethereum address format
    }
    
    // Add more specific validation for other coin types if needed
    return address.length >= 26 && address.length <= 75; // Generic length check
}

// Helper function to generate element IDs
function generateElementId(prefix, coinType, random) {
    return _.escape(prefix + '-' + coinType + '-' + random);
}

if (Array.isArray(all_coin_wall_add) && all_coin_wall_add.length > 0) {
    _.each(all_coin_wall_add, function(address) {
        // Validate required fields
        if (!address || !address.ccewd_coin_list) {
            output.push('<div class="ccewd-error">Invalid coin configuration detected</div>');
            return;
        }

        var coin_type = _.escape(address.ccewd_coin_list),
            wallet_address = _.escape(address.ccewd_wallet_address || ''),
            wallet_meta = _.escape(address.ccewd_wallet_address_meta || ''),
            active_tab = (i === 0) ? 'current' : '',
            element_id = generateElementId('wallet', coin_type, random);

        // Validate wallet address if provided
        if (wallet_address && !isValidWalletAddress(wallet_address, coin_type)) {
            output.push('<div class="ccewd-error">Invalid wallet address format for ' + _.escape(coin_type) + '</div>');
            return;
        }

        var coin_name = (coin_type === 'metamask') ? 'MetaMask' : _.escape(coin_type.replace(/-/g, ' ')),
            coin_name_capitalized = coin_name.charAt(0).toUpperCase() + coin_name.slice(1),
            title_content = title.replace('[coin-name]', coin_name_capitalized),
            desc_content = description.replace('[coin-name]', coin_name_capitalized),
            coin_logo = _.escape(settings.ccewd_url + '/donation-box/assets/logos/' + coin_type + '.svg');

        // Build logo HTML with proper alt text
        var logo_html = '<img src="' + coin_logo + '" alt="' + coin_name_capitalized + ' Logo" class="ccewd-coin-logo"> ' + 
                       '<span class="ccewd-coin-name">' + coin_name_capitalized + '</span>';
        
        // Add coin to navigation with ARIA attributes
        coin_links.push(
            '<li class="ccewd-coins ' + active_tab + '" id="' + coin_type + '" ' +
            'data-tab="' + coin_type + '-tab" role="tab" aria-selected="' + (active_tab ? 'true' : 'false') + '" ' +
            'aria-controls="' + coin_type + '-tab">' + logo_html + '</li>'
        );

        if (design_type === 'list') {
            if (wallet_address && coin_type !== 'metamask') {
                classic_list.push(
                    '<li class="ccewd-classic-list">' +
                    '<h2 class="ccewd-title">' + title_content + '</h2>' +
                    '<div id="qrcode-' + element_id + '" class="ccewd_qr_code" data-qr-id="qrcode-' + element_id + '" ' +
                    'aria-label="QR Code for ' + coin_name_capitalized + ' wallet address"></div>' +
                    '<div class="ccewd_classic_input_add">' +
                    '<input type="text" class="wallet-address-input" id="' + element_id + '" ' +
                    'name="' + coin_type + '-classic-wallet-address" value="' + wallet_address + '" ' +
                    'data-input-id-js="' + element_id + '" readonly aria-label="' + coin_name_capitalized + ' wallet address">' +
                    '<button class="ccewd_btn" data-clipboard-target="#' + element_id + '" ' +
                    'aria-label="Copy ' + coin_name_capitalized + ' wallet address">COPY</button>' +
                    '</div>' +
                    (wallet_meta ? '<div class="ccewd_tag" role="note"><span class="ccewd_tag_heading">Tag/Note:- </span>' + wallet_meta + '</div>' : '') +
                    '</li>'
                );
            } else if (coin_type === 'metamask' && wallet_address) {
                classic_list.push(
                    '<li class="ccewd-classic-list">' +
                    '<h2 class="ccewd-title">' + cdb_metamask_title + '</h2>' +
                    '<div class="tip-button" data-metamask-address="' + wallet_address + '" ' +
                    'data-metamask-amount="' + metamask_price + '" role="button" ' +
                    'aria-label="Donate ' + metamask_price + ' ETH via MetaMask"></div>' +
                    (wallet_meta ? '<div class="ccewd_tag" role="note"><span class="ccewd_tag_heading">Tag/Note:- </span>' + wallet_meta + '</div>' : '') +
                    '<div class="message" role="status" aria-live="polite"></div>' +
                    '</li>'
                );
            } else {
                classic_list.push(
                    '<li class="ccewd-classic-list">' +
                    '<div class="message ccewd-warning" role="alert">' + 
                    (coin_type === 'select' ? 'Please select a cryptocurrency' : 'Please enter a valid wallet address') + 
                    '</div>' +
                    '</li>'
                );
            }
        } else {
            var tab_content = [];
            tab_content.push(
                '<div class="ccewd-tabs-content ' + active_tab + '" id="' + coin_type + '-tab" ' +
                'role="tabpanel" aria-labelledby="' + coin_type + '">'
            );

            if (wallet_address && coin_type !== 'metamask') {
                tab_content.push(
                    '<div id="qrcode-' + element_id + '" class="ccewd_qr_code" data-qr-id="qrcode-' + element_id + '" ' +
                    'aria-label="QR Code for ' + coin_name_capitalized + ' wallet address"></div>' +
                    '<div class="ccewd_input_add">' +
                    '<h2 class="ccewd-title">' + title_content + '</h2>' +
                    '<p class="ccewd-desc">' + desc_content + '</p>' +
                    (wallet_meta ? '<div class="ccewd_tag" role="note"><span class="ccewd_tag_heading">Tag/Note:- </span>' + wallet_meta + '</div>' : '') +
                    '<input type="text" class="wallet-address-input" id="' + element_id + '" ' +
                    'name="' + coin_type + '-wallet-address" value="' + wallet_address + '" ' +
                    'data-input-id-js="' + element_id + '" readonly aria-label="' + coin_name_capitalized + ' wallet address">' +
                    '<button class="ccewd_btn" data-clipboard-target="#' + element_id + '" ' +
                    'aria-label="Copy ' + coin_name_capitalized + ' wallet address">COPY</button>' +
                    '</div>'
                );
            } else if (coin_type === 'metamask' && wallet_address) {
                tab_content.push(
                    '<div class="cdb-metamask-wrapper">' +
                    '<h2 class="ccewd-title">' + cdb_metamask_title + '</h2>' +
                    '<p class="ccewd-desc">' + metamask_description + '</p>' +
                    '<div class="tip-button" data-metamask-address="' + wallet_address + '" ' +
                    'data-metamask-amount="' + metamask_price + '" role="button" ' +
                    'aria-label="Donate ' + metamask_price + ' ETH via MetaMask"></div>' +
                    (wallet_meta ? '<div class="ccewd_tag" role="note"><span class="ccewd_tag_heading">Tag/Note:- </span>' + wallet_meta + '</div>' : '') +
                    '<div class="message" role="status" aria-live="polite"></div>' +
                    '</div>'
                );
            } else {
                tab_content.push(
                    '<div class="message ccewd-warning" role="alert">' +
                    (coin_type === 'select' ? 'Please select a cryptocurrency' : 'Please enter a valid wallet address') +
                    '</div>'
                );
            }

            tab_content.push('</div>');
            coin_tabs.push(tab_content.join(''));
        }

        i++;
    });

    if (design_type === 'list') {
        output.push(
            '<div class="ccewd-classic-container" role="region" aria-label="Cryptocurrency donation addresses">' +
            '<ul class="ccewd-classic-list">' +
            classic_list.join('') +
            '</ul>' +
            '</div>'
        );
    } else {
        output.push(
            '<div class="ccewd-container" id="ccewd-random-' + random + '" role="tablist" aria-label="Cryptocurrency donation options">' +
            '<ul class="ccewd-tabs" id="ccewd-coin-list">' + coin_links.join('') + '</ul>' +
            coin_tabs.join('') +
            '</div>'
        );
    }
} else {
    output.push(
        '<div class="ccewd-error" role="alert">' +
        '<h6>Please add cryptocurrency wallet addresses in the plugin settings panel</h6>' +
        '</div>'
    );
}

print(output.join(''));
#>