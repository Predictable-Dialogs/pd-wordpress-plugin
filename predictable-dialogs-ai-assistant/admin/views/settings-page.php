<?php

if (!defined('ABSPATH')) {
    exit;
}

$preddiai_option_name = PREDDIAI_Settings::OPTION_NAME;
$preddiai_settings_page_url = admin_url('admin-post.php');
$preddiai_is_connected = !empty($preddiai_settings['agent_name']);
$preddiai_agent_label = !empty($preddiai_settings['agent_display_name'])
    ? $preddiai_settings['agent_display_name']
    : $preddiai_settings['agent_name'];
$preddiai_widget_types = array('Bubble', 'Popup', 'Standard');
$preddiai_widget_type = in_array($preddiai_settings['widget_type'], $preddiai_widget_types, true)
    ? $preddiai_settings['widget_type']
    : 'Standard';
$preddiai_customize_widget_labels = array(
    'Standard' => __('Customize Standard Widget', 'predictable-dialogs-ai-assistant'),
    'Bubble' => __('Customize Bubble Widget', 'predictable-dialogs-ai-assistant'),
    'Popup' => __('Customize Popup Widget', 'predictable-dialogs-ai-assistant'),
);
$preddiai_customize_widget_label = isset($preddiai_customize_widget_labels[$preddiai_widget_type])
    ? $preddiai_customize_widget_labels[$preddiai_widget_type]
    : $preddiai_customize_widget_labels['Standard'];
$preddiai_pages = get_pages(
    array(
        'sort_column' => 'post_title',
        'sort_order' => 'ASC',
    )
);
$preddiai_standard_shortcode = sprintf(
    '[preddiai agent="%s" width="%s" height="%s"]',
    $preddiai_settings['agent_name'],
    $preddiai_settings['standard_width'],
    $preddiai_settings['standard_height']
);
$preddiai_standard_width_options = array('100%', '75%', '50%', '960px', '1200px', 'auto');
$preddiai_standard_height_options = array('400px', '500px', '600px', '700px', '800px', '100vh');
$preddiai_status = isset($_GET['preddiai_status']) ? sanitize_key(wp_unslash($_GET['preddiai_status'])) : '';
$preddiai_message = isset($_GET['preddiai_message']) ? sanitize_key(wp_unslash($_GET['preddiai_message'])) : '';
$preddiai_settings_saved = isset($_GET['settings-updated']) && sanitize_text_field(wp_unslash($_GET['settings-updated'])) === 'true';
$preddiai_notices = array(
    'connected' => __('Predictable Dialogs connected successfully.', 'predictable-dialogs-ai-assistant'),
    'disconnected' => __('Predictable Dialogs disconnected.', 'predictable-dialogs-ai-assistant'),
    'connect_failed' => __('Could not start the Predictable Dialogs connection.', 'predictable-dialogs-ai-assistant'),
    'state_failed' => __('Connection verification failed. Please start again from WordPress.', 'predictable-dialogs-ai-assistant'),
    'expired' => __('Connection expired. Please start again.', 'predictable-dialogs-ai-assistant'),
    'exchange_failed' => __('Could not complete the Predictable Dialogs connection.', 'predictable-dialogs-ai-assistant'),
);
$preddiai_color_presets = array(
    '#104EC0' => __('Blue', 'predictable-dialogs-ai-assistant'),
    '#3EDA58' => __('Green', 'predictable-dialogs-ai-assistant'),
    '#F93B25' => __('Red', 'predictable-dialogs-ai-assistant'),
    '#FB960A' => __('Orange', 'predictable-dialogs-ai-assistant'),
    '#8F9094' => __('Grey', 'predictable-dialogs-ai-assistant'),
);
$preddiai_read_admin_asset = static function ($asset_name) {
    $asset_path = PREDDIAI_PLUGIN_DIR . 'admin/assets/' . ltrim((string) $asset_name, '/');

    if (!is_readable($asset_path)) {
        return '';
    }

    return trim((string) file_get_contents($asset_path));
};
$preddiai_bubble_default_icon_svg = $preddiai_read_admin_asset('bubble-default-icon.svg');
$preddiai_logo_url = PREDDIAI_PLUGIN_URL . 'admin/assets/pd-logo-256x256.png';
?>
<div class="wrap preddiai-settings-shell">
  <template id="preddiai-bubble-default-icon-template">
    <?php echo $preddiai_bubble_default_icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plugin-owned SVG asset. ?>
  </template>

  <div class="preddiai-header">
    <img
      class="preddiai-header-logo"
      src="<?php echo esc_url($preddiai_logo_url); ?>"
      width="40"
      height="40"
      alt=""
      aria-hidden="true"
    />
    <h1><?php esc_html_e('Predictable Dialogs', 'predictable-dialogs-ai-assistant'); ?></h1>
  </div>

  <?php if ($preddiai_message && isset($preddiai_notices[$preddiai_message])) : ?>
    <div class="notice notice-<?php echo esc_attr($preddiai_status === 'success' ? 'success' : 'error'); ?> is-dismissible">
      <p><?php echo esc_html($preddiai_notices[$preddiai_message]); ?></p>
    </div>
  <?php endif; ?>

  <div class="preddiai-connection-panel">
    <?php if ($preddiai_is_connected) : ?>
      <div class="preddiai-connected-summary">
        <span class="preddiai-status-pill"><?php esc_html_e('Installed', 'predictable-dialogs-ai-assistant'); ?></span>
        <div>
          <p class="preddiai-summary-label"><?php esc_html_e('Agent', 'predictable-dialogs-ai-assistant'); ?></p>
          <p class="preddiai-agent-name"><?php echo esc_html($preddiai_agent_label); ?></p>
        </div>
      </div>
      <div class="preddiai-connection-actions">
        <form method="post" action="<?php echo esc_url($preddiai_settings_page_url); ?>">
          <input type="hidden" name="action" value="preddiai_connect" />
          <?php wp_nonce_field(PREDDIAI_Auth::AUTH_NONCE_ACTION, PREDDIAI_Auth::AUTH_NONCE_NAME); ?>
          <?php submit_button(__('Change', 'predictable-dialogs-ai-assistant'), 'primary', 'preddiai-connect', false); ?>
        </form>
        <form method="post" action="<?php echo esc_url($preddiai_settings_page_url); ?>">
          <input type="hidden" name="action" value="preddiai_disconnect" />
          <?php wp_nonce_field(PREDDIAI_Auth::AUTH_NONCE_ACTION, PREDDIAI_Auth::AUTH_NONCE_NAME); ?>
          <?php submit_button(__('Disconnect', 'predictable-dialogs-ai-assistant'), 'secondary', 'preddiai-disconnect', false); ?>
        </form>
      </div>
    <?php else : ?>
      <p><?php esc_html_e('Add your AI agent to this WordPress website.', 'predictable-dialogs-ai-assistant'); ?></p>
      <form method="post" action="<?php echo esc_url($preddiai_settings_page_url); ?>">
        <input type="hidden" name="action" value="preddiai_connect" />
        <?php wp_nonce_field(PREDDIAI_Auth::AUTH_NONCE_ACTION, PREDDIAI_Auth::AUTH_NONCE_NAME); ?>
        <?php submit_button(__('Connect to Predictable Dialogs', 'predictable-dialogs-ai-assistant'), 'primary', 'preddiai-connect', false); ?>
      </form>
    <?php endif; ?>
  </div>

  <form method="post" action="options.php" class="preddiai-settings-form">
    <?php settings_fields(PREDDIAI_Settings::SETTINGS_GROUP); ?>

    <input type="hidden" name="<?php echo esc_attr($preddiai_option_name); ?>[agent_name]" value="<?php echo esc_attr($preddiai_settings['agent_name']); ?>" />
    <input type="hidden" name="<?php echo esc_attr($preddiai_option_name); ?>[agent_display_name]" value="<?php echo esc_attr($preddiai_settings['agent_display_name']); ?>" />

    <section class="preddiai-settings-section">
      <h2><?php esc_html_e('Widget type', 'predictable-dialogs-ai-assistant'); ?></h2>
      <p class="description">
        <?php esc_html_e('Choose how the agent appears on this WordPress website.', 'predictable-dialogs-ai-assistant'); ?>
      </p>

      <div class="preddiai-field-row">
        <label for="preddiai-widget-type"><?php esc_html_e('Widget type', 'predictable-dialogs-ai-assistant'); ?></label>
        <select id="preddiai-widget-type" name="<?php echo esc_attr($preddiai_option_name); ?>[widget_type]">
          <option value="Bubble" <?php selected($preddiai_widget_type, 'Bubble'); ?>><?php esc_html_e('Bubble', 'predictable-dialogs-ai-assistant'); ?></option>
          <option value="Popup" <?php selected($preddiai_widget_type, 'Popup'); ?>><?php esc_html_e('Popup', 'predictable-dialogs-ai-assistant'); ?></option>
          <option value="Standard" <?php selected($preddiai_widget_type, 'Standard'); ?>><?php esc_html_e('Standard', 'predictable-dialogs-ai-assistant'); ?></option>
        </select>
      </div>
    </section>

    <details class="preddiai-widget-customization">
      <summary>
        <span
          data-widget-customization-title
          data-standard-title="<?php echo esc_attr($preddiai_customize_widget_labels['Standard']); ?>"
          data-bubble-title="<?php echo esc_attr($preddiai_customize_widget_labels['Bubble']); ?>"
          data-popup-title="<?php echo esc_attr($preddiai_customize_widget_labels['Popup']); ?>"
        ><?php echo esc_html($preddiai_customize_widget_label); ?></span>
      </summary>
      <div class="preddiai-widget-panel" data-widget-panel="Standard">
        <h3><?php esc_html_e('Customize Standard', 'predictable-dialogs-ai-assistant'); ?></h3>
        <p class="description">
          <?php esc_html_e('Use the Standard widget inline in your page content, either automatically from the plugin or with a copied shortcode.', 'predictable-dialogs-ai-assistant'); ?>
        </p>

        <div class="preddiai-two-column">
          <label for="preddiai-standard-width">
            <?php esc_html_e('Width', 'predictable-dialogs-ai-assistant'); ?>
            <input
              type="text"
              id="preddiai-standard-width"
              name="<?php echo esc_attr($preddiai_option_name); ?>[standard_width]"
              value="<?php echo esc_attr($preddiai_settings['standard_width']); ?>"
              placeholder="100%"
              list="preddiai-standard-width-options"
              data-standard-shortcode-width
            />
            <datalist id="preddiai-standard-width-options">
              <?php foreach ($preddiai_standard_width_options as $preddiai_standard_width_option) : ?>
                <option value="<?php echo esc_attr($preddiai_standard_width_option); ?>"></option>
              <?php endforeach; ?>
            </datalist>
          </label>
          <label for="preddiai-standard-height">
            <?php esc_html_e('Height', 'predictable-dialogs-ai-assistant'); ?>
            <input
              type="text"
              id="preddiai-standard-height"
              name="<?php echo esc_attr($preddiai_option_name); ?>[standard_height]"
              value="<?php echo esc_attr($preddiai_settings['standard_height']); ?>"
              placeholder="600px"
              list="preddiai-standard-height-options"
              data-standard-shortcode-height
            />
            <datalist id="preddiai-standard-height-options">
              <?php foreach ($preddiai_standard_height_options as $preddiai_standard_height_option) : ?>
                <option value="<?php echo esc_attr($preddiai_standard_height_option); ?>"></option>
              <?php endforeach; ?>
            </datalist>
          </label>
        </div>
        <p class="description">
          <?php esc_html_e('Choose a preset or type a custom value such as 100%, 960px, 600px, or 100vh.', 'predictable-dialogs-ai-assistant'); ?>
        </p>

        <div class="preddiai-field-row">
          <label for="preddiai-standard-placement-mode"><?php esc_html_e('Placement', 'predictable-dialogs-ai-assistant'); ?></label>
          <div>
            <select
              id="preddiai-standard-placement-mode"
              name="<?php echo esc_attr($preddiai_option_name); ?>[standard_placement_mode]"
            >
              <option value="manual" <?php selected($preddiai_settings['standard_placement_mode'], 'manual'); ?>><?php esc_html_e('Manual shortcode', 'predictable-dialogs-ai-assistant'); ?></option>
              <option value="selected_page" <?php selected($preddiai_settings['standard_placement_mode'], 'selected_page'); ?>><?php esc_html_e('Selected page', 'predictable-dialogs-ai-assistant'); ?></option>
              <option value="all_pages" <?php selected($preddiai_settings['standard_placement_mode'], 'all_pages'); ?>><?php esc_html_e('All pages', 'predictable-dialogs-ai-assistant'); ?></option>
            </select>
            <p class="description">
              <?php esc_html_e('Manual mode only generates the shortcode. The other modes place the Standard widget automatically.', 'predictable-dialogs-ai-assistant'); ?>
            </p>
          </div>
        </div>

        <div class="preddiai-field-row preddiai-standard-shortcode-field" data-standard-shortcode-field <?php echo $preddiai_settings['standard_placement_mode'] === 'manual' ? '' : 'hidden'; ?>>
          <label for="preddiai-standard-shortcode"><?php esc_html_e('Shortcode', 'predictable-dialogs-ai-assistant'); ?></label>
          <div>
            <div class="preddiai-standard-shortcode-row">
              <input
                type="text"
                id="preddiai-standard-shortcode"
                class="large-text code"
                value="<?php echo esc_attr($preddiai_standard_shortcode); ?>"
                readonly
                data-standard-shortcode
                data-shortcode-agent="<?php echo esc_attr($preddiai_settings['agent_name']); ?>"
                <?php disabled(!$preddiai_is_connected); ?>
              />
              <button
                type="button"
                class="button"
                data-copy-target="preddiai-standard-shortcode"
                <?php disabled(!$preddiai_is_connected); ?>
              >
                <?php esc_html_e('Copy', 'predictable-dialogs-ai-assistant'); ?>
              </button>
            </div>
            <p class="description">
              <?php esc_html_e('Copy this shortcode into any WordPress page or block when you want to place the widget manually.', 'predictable-dialogs-ai-assistant'); ?>
            </p>
            <?php if (!$preddiai_is_connected) : ?>
              <p class="description">
                <?php esc_html_e('Connect an agent first to generate a usable shortcode.', 'predictable-dialogs-ai-assistant'); ?>
              </p>
            <?php endif; ?>
          </div>
        </div>

        <div class="preddiai-field-row preddiai-standard-content-position-field" data-standard-content-position-field <?php echo $preddiai_settings['standard_placement_mode'] === 'manual' ? 'hidden' : ''; ?>>
          <label for="preddiai-standard-content-position"><?php esc_html_e('Position', 'predictable-dialogs-ai-assistant'); ?></label>
          <div>
            <select
              id="preddiai-standard-content-position"
              name="<?php echo esc_attr($preddiai_option_name); ?>[standard_content_position]"
            >
              <option value="above_content" <?php selected($preddiai_settings['standard_content_position'], 'above_content'); ?>><?php esc_html_e('Above all content', 'predictable-dialogs-ai-assistant'); ?></option>
              <option value="below_content" <?php selected($preddiai_settings['standard_content_position'], 'below_content'); ?>><?php esc_html_e('Below all content', 'predictable-dialogs-ai-assistant'); ?></option>
            </select>
            <p class="description">
              <?php esc_html_e('To place the widget exactly where you want it, choose Manual shortcode and add the shortcode to that area.', 'predictable-dialogs-ai-assistant'); ?>
            </p>
          </div>
        </div>

        <div class="preddiai-field-row preddiai-standard-page-field" data-standard-page-field <?php echo $preddiai_settings['standard_placement_mode'] === 'selected_page' ? '' : 'hidden'; ?>>
          <label for="preddiai-standard-page-id"><?php esc_html_e('Page', 'predictable-dialogs-ai-assistant'); ?></label>
          <div>
            <select
              id="preddiai-standard-page-id"
              name="<?php echo esc_attr($preddiai_option_name); ?>[standard_page_id]"
            >
              <option value="0"><?php esc_html_e('Select a page', 'predictable-dialogs-ai-assistant'); ?></option>
              <?php foreach ($preddiai_pages as $preddiai_page) : ?>
                <option value="<?php echo esc_attr($preddiai_page->ID); ?>" <?php selected((int) $preddiai_settings['standard_page_id'], (int) $preddiai_page->ID); ?>>
                  <?php echo esc_html($preddiai_page->post_title); ?>
                </option>
              <?php endforeach; ?>
            </select>
            <p class="description">
              <?php esc_html_e('The widget will be appended after the content on this page.', 'predictable-dialogs-ai-assistant'); ?>
            </p>
          </div>
        </div>
      </div>

      <div class="preddiai-widget-panel" data-widget-panel="Popup">
        <h3><?php esc_html_e('Customize Popup', 'predictable-dialogs-ai-assistant'); ?></h3>
        <div class="preddiai-inline-controls preddiai-scroll-controls">
          <span class="preddiai-control-title"><?php esc_html_e('Auto show', 'predictable-dialogs-ai-assistant'); ?></span>
          <label class="preddiai-switch" for="preddiai-popup-auto-show">
            <input type="hidden" name="<?php echo esc_attr($preddiai_option_name); ?>[popup_auto_show_enabled]" value="0" />
            <input
              type="checkbox"
              id="preddiai-popup-auto-show"
              name="<?php echo esc_attr($preddiai_option_name); ?>[popup_auto_show_enabled]"
              value="1"
              <?php checked(!empty($preddiai_settings['popup_auto_show_enabled'])); ?>
            />
            <span class="preddiai-switch-slider" aria-hidden="true"></span>
          </label>
          <span class="preddiai-muted"><?php esc_html_e('On/Off', 'predictable-dialogs-ai-assistant'); ?></span>
          <span class="preddiai-delay-controls" data-toggle-source="preddiai-popup-auto-show">
            <span class="preddiai-control-title"><?php esc_html_e('After', 'predictable-dialogs-ai-assistant'); ?></span>
            <button type="button" class="button preddiai-stepper-button" data-step-target="preddiai-popup-delay" data-step="-1">-</button>
            <input
              type="number"
              id="preddiai-popup-delay"
              name="<?php echo esc_attr($preddiai_option_name); ?>[popup_auto_show_delay_seconds]"
              min="1"
              step="1"
              value="<?php echo esc_attr($preddiai_settings['popup_auto_show_delay_seconds']); ?>"
            />
            <button type="button" class="button preddiai-stepper-button" data-step-target="preddiai-popup-delay" data-step="1">+</button>
            <span class="preddiai-control-title"><?php esc_html_e('seconds', 'predictable-dialogs-ai-assistant'); ?></span>
          </span>
        </div>
      </div>

      <div class="preddiai-widget-panel" data-widget-panel="Bubble">
        <h3><?php esc_html_e('Customize Bubble', 'predictable-dialogs-ai-assistant'); ?></h3>

        <div class="preddiai-bubble-designer">
          <div>
            <h4><?php esc_html_e('Pick a color', 'predictable-dialogs-ai-assistant'); ?></h4>
            <div class="preddiai-color-row" role="group" aria-label="<?php esc_attr_e('Bubble colors', 'predictable-dialogs-ai-assistant'); ?>">
              <?php foreach ($preddiai_color_presets as $preddiai_color => $preddiai_label) : ?>
                <button
                  type="button"
                  class="preddiai-color-swatch"
                  style="background-color: <?php echo esc_attr($preddiai_color); ?>"
                  data-color="<?php echo esc_attr($preddiai_color); ?>"
                  aria-label="<?php echo esc_attr($preddiai_label); ?>"
                >
                  <span aria-hidden="true">&#10003;</span>
                </button>
              <?php endforeach; ?>
            </div>
            <div class="preddiai-custom-color">
              <label for="preddiai-bubble-button-color" class="preddiai-custom-color-button">
                <span aria-hidden="true">+</span>
                <input
                  type="color"
                  id="preddiai-bubble-button-color"
                  name="<?php echo esc_attr($preddiai_option_name); ?>[bubble_button_color]"
                  value="<?php echo esc_attr($preddiai_settings['bubble_button_color']); ?>"
                />
              </label>
              <span class="preddiai-muted"><?php esc_html_e('Custom color - pick any shade', 'predictable-dialogs-ai-assistant'); ?></span>
              <strong id="preddiai-bubble-color-label"><?php echo esc_html(strtoupper($preddiai_settings['bubble_button_color'])); ?></strong>
            </div>
          </div>

          <div class="preddiai-bubble-icon-column">
            <h4><?php esc_html_e('Bubble Icon', 'predictable-dialogs-ai-assistant'); ?></h4>
            <div class="preddiai-bubble-icon-preview" aria-hidden="true">
              <?php echo $preddiai_bubble_default_icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plugin-owned SVG asset. ?>
            </div>
            <label for="preddiai-bubble-custom-icon-src" class="screen-reader-text"><?php esc_html_e('Custom bubble icon URL', 'predictable-dialogs-ai-assistant'); ?></label>
            <input
              type="url"
              id="preddiai-bubble-custom-icon-src"
              name="<?php echo esc_attr($preddiai_option_name); ?>[bubble_custom_icon_src]"
              value="<?php echo esc_attr($preddiai_settings['bubble_custom_icon_src']); ?>"
              placeholder="<?php esc_attr_e('Custom icon URL', 'predictable-dialogs-ai-assistant'); ?>"
              class="regular-text"
            />
            <p class="description">
              <?php esc_html_e('Need an image URL? In Predictable Dialogs, choose Copy the embed code into your site, select Bubble, then use Customize Bubble to upload an icon and copy its URL.', 'predictable-dialogs-ai-assistant'); ?>
            </p>
          </div>
        </div>

        <div class="preddiai-two-column">
          <label>
            <?php esc_html_e('Placement', 'predictable-dialogs-ai-assistant'); ?>
            <select name="<?php echo esc_attr($preddiai_option_name); ?>[bubble_placement]">
              <option value="right" <?php selected($preddiai_settings['bubble_placement'], 'right'); ?>><?php esc_html_e('Right', 'predictable-dialogs-ai-assistant'); ?></option>
              <option value="left" <?php selected($preddiai_settings['bubble_placement'], 'left'); ?>><?php esc_html_e('Left', 'predictable-dialogs-ai-assistant'); ?></option>
            </select>
          </label>
          <label>
            <?php esc_html_e('Button size', 'predictable-dialogs-ai-assistant'); ?>
            <select name="<?php echo esc_attr($preddiai_option_name); ?>[bubble_button_size]">
              <option value="medium" <?php selected($preddiai_settings['bubble_button_size'], 'medium'); ?>><?php esc_html_e('Medium', 'predictable-dialogs-ai-assistant'); ?></option>
              <option value="large" <?php selected($preddiai_settings['bubble_button_size'], 'large'); ?>><?php esc_html_e('Large', 'predictable-dialogs-ai-assistant'); ?></option>
            </select>
          </label>
        </div>

        <div class="preddiai-preview-stage">
          <div class="preddiai-preview-message">
            <?php if (!empty($preddiai_settings['bubble_preview_message_avatar_url'])) : ?>
              <img src="<?php echo esc_url($preddiai_settings['bubble_preview_message_avatar_url']); ?>" alt="" />
            <?php endif; ?>
            <span><?php echo esc_html($preddiai_settings['bubble_preview_message_text']); ?></span>
          </div>
          <div class="preddiai-preview-button" aria-hidden="true">
            <?php echo $preddiai_bubble_default_icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Plugin-owned SVG asset. ?>
          </div>
        </div>

        <div class="preddiai-divider"></div>

        <div class="preddiai-inline-controls">
          <span class="preddiai-control-title"><?php esc_html_e('Preview message', 'predictable-dialogs-ai-assistant'); ?></span>
          <label class="preddiai-switch" for="preddiai-preview-message-enabled">
            <input type="hidden" name="<?php echo esc_attr($preddiai_option_name); ?>[bubble_preview_message_enabled]" value="0" />
            <input
              type="checkbox"
              id="preddiai-preview-message-enabled"
              name="<?php echo esc_attr($preddiai_option_name); ?>[bubble_preview_message_enabled]"
              value="1"
              <?php checked(!empty($preddiai_settings['bubble_preview_message_enabled'])); ?>
            />
            <span class="preddiai-switch-slider" aria-hidden="true"></span>
          </label>
          <span class="preddiai-muted"><?php esc_html_e('On/Off', 'predictable-dialogs-ai-assistant'); ?></span>
        </div>

        <div class="preddiai-preview-message-settings">
          <label for="preddiai-preview-message-text"><?php esc_html_e('Preview message text', 'predictable-dialogs-ai-assistant'); ?></label>
          <input
            type="text"
            id="preddiai-preview-message-text"
            name="<?php echo esc_attr($preddiai_option_name); ?>[bubble_preview_message_text]"
            value="<?php echo esc_attr($preddiai_settings['bubble_preview_message_text']); ?>"
            class="large-text"
          />

          <label for="preddiai-preview-message-avatar-url"><?php esc_html_e('Preview avatar', 'predictable-dialogs-ai-assistant'); ?></label>
          <input
            type="url"
            id="preddiai-preview-message-avatar-url"
            name="<?php echo esc_attr($preddiai_option_name); ?>[bubble_preview_message_avatar_url]"
            value="<?php echo esc_attr($preddiai_settings['bubble_preview_message_avatar_url']); ?>"
            placeholder="<?php esc_attr_e('Avatar image URL', 'predictable-dialogs-ai-assistant'); ?>"
            class="large-text"
          />
          <p class="description">
            <?php esc_html_e('Optional. Paste an image URL to show an avatar in the preview message. For testing, you can try https://i.pravatar.cc/300?u=9940919.', 'predictable-dialogs-ai-assistant'); ?>
          </p>

          <div class="preddiai-inline-controls preddiai-scroll-controls">
            <span class="preddiai-control-title"><?php esc_html_e('Auto show preview message', 'predictable-dialogs-ai-assistant'); ?></span>
            <label class="preddiai-switch" for="preddiai-preview-auto-show">
              <input type="hidden" name="<?php echo esc_attr($preddiai_option_name); ?>[bubble_preview_auto_show_enabled]" value="0" />
              <input
                type="checkbox"
                id="preddiai-preview-auto-show"
                name="<?php echo esc_attr($preddiai_option_name); ?>[bubble_preview_auto_show_enabled]"
                value="1"
                <?php checked(!empty($preddiai_settings['bubble_preview_auto_show_enabled'])); ?>
              />
              <span class="preddiai-switch-slider" aria-hidden="true"></span>
            </label>
            <span class="preddiai-muted"><?php esc_html_e('On/Off', 'predictable-dialogs-ai-assistant'); ?></span>
            <span class="preddiai-delay-controls" data-toggle-source="preddiai-preview-auto-show">
              <span class="preddiai-control-title"><?php esc_html_e('After', 'predictable-dialogs-ai-assistant'); ?></span>
              <button type="button" class="button preddiai-stepper-button" data-step-target="preddiai-preview-delay" data-step="-1">-</button>
              <input
                type="number"
                id="preddiai-preview-delay"
                name="<?php echo esc_attr($preddiai_option_name); ?>[bubble_preview_auto_show_delay_seconds]"
                min="1"
                step="1"
                value="<?php echo esc_attr($preddiai_settings['bubble_preview_auto_show_delay_seconds']); ?>"
              />
              <button type="button" class="button preddiai-stepper-button" data-step-target="preddiai-preview-delay" data-step="1">+</button>
              <span class="preddiai-control-title"><?php esc_html_e('seconds', 'predictable-dialogs-ai-assistant'); ?></span>
            </span>
          </div>
        </div>
      </div>
    </details>

    <details class="preddiai-manual-settings">
      <summary><?php esc_html_e('Manual initialization snippet', 'predictable-dialogs-ai-assistant'); ?></summary>
      <p class="description">
        <?php esc_html_e('This is the install code the plugin renders. Connecting to Predictable Dialogs fills it for you; edits here override the connected snippet.', 'predictable-dialogs-ai-assistant'); ?>
      </p>
      <div class="preddiai-snippet-actions">
        <button type="button" class="button" data-copy-target="preddiai-snippet">
          <?php esc_html_e('Copy code', 'predictable-dialogs-ai-assistant'); ?>
        </button>
      </div>
      <textarea
        id="preddiai-snippet"
        name="<?php echo esc_attr($preddiai_option_name); ?>[initialization_snippet]"
        rows="9"
        cols="80"
        class="large-text code preddiai-code-textarea"
        spellcheck="false"
        autocapitalize="off"
        autocorrect="off"
        data-agent-name="<?php echo esc_attr($preddiai_settings['agent_name']); ?>"
        data-embed-cdn-url="<?php echo esc_url(PREDDIAI_Frontend_Renderer::EMBED_CDN_URL); ?>"
        placeholder="<?php esc_attr_e('Paste the WordPress initialization snippet here.', 'predictable-dialogs-ai-assistant'); ?>"
      ><?php echo esc_textarea($preddiai_settings['initialization_snippet']); ?></textarea>
    </details>

    <details class="preddiai-site-controls">
      <summary><?php esc_html_e('Site controls', 'predictable-dialogs-ai-assistant'); ?></summary>
      <div class="preddiai-field-row">
        <label for="preddiai-excluded-pages"><?php esc_html_e('Excluded pages', 'predictable-dialogs-ai-assistant'); ?></label>
        <input
          type="text"
          id="preddiai-excluded-pages"
          name="<?php echo esc_attr($preddiai_option_name); ?>[excluded_pages]"
          class="regular-text"
          value="<?php echo esc_attr($preddiai_settings['excluded_pages']); ?>"
          placeholder="/app/*, /app?param=*"
        />
        <p class="description">
          <?php esc_html_e('Comma-separated patterns. Supported examples:', 'predictable-dialogs-ai-assistant'); ?>
          <code>/app/*</code>,
          <code>/app</code>,
          <code>/app?param=1</code>,
          <code>/app?param=*</code>,
          <code>/app/*?param=*</code>
        </p>
      </div>

      <div class="preddiai-field-row">
        <label for="preddiai-include-user"><?php esc_html_e('Inject logged-in WordPress user', 'predictable-dialogs-ai-assistant'); ?></label>
        <label>
          <input type="hidden" name="<?php echo esc_attr($preddiai_option_name); ?>[include_logged_in_user]" value="0" />
          <input
            type="checkbox"
            id="preddiai-include-user"
            name="<?php echo esc_attr($preddiai_option_name); ?>[include_logged_in_user]"
            value="1"
            <?php checked(!empty($preddiai_settings['include_logged_in_user'])); ?>
          />
          <?php esc_html_e('Pass logged-in user data to the widget as Preddiai.user', 'predictable-dialogs-ai-assistant'); ?>
        </label>
      </div>

      <div class="preddiai-field-row">
        <label for="preddiai-disable-widget"><?php esc_html_e('Disable widget', 'predictable-dialogs-ai-assistant'); ?></label>
        <div class="preddiai-toggle-control">
          <label class="preddiai-switch" for="preddiai-disable-widget">
            <input type="hidden" name="<?php echo esc_attr($preddiai_option_name); ?>[disable_widget]" value="0" />
            <input
              type="checkbox"
              id="preddiai-disable-widget"
              name="<?php echo esc_attr($preddiai_option_name); ?>[disable_widget]"
              value="1"
              <?php checked(!empty($preddiai_settings['disable_widget'])); ?>
            />
            <span class="preddiai-switch-slider" aria-hidden="true"></span>
          </label>
          <span
            id="preddiai-disable-widget-status"
            class="preddiai-toggle-status"
            data-disabled-label="<?php echo esc_attr__('Disabled', 'predictable-dialogs-ai-assistant'); ?>"
          ></span>
        </div>
      </div>
    </details>

    <div
      class="preddiai-save-bar"
      data-save-bar
      data-saved-on-load="<?php echo esc_attr($preddiai_settings_saved ? '1' : '0'); ?>"
      hidden
    >
      <div class="preddiai-save-bar-inner">
        <span
          class="preddiai-save-status"
          data-save-status
          data-unsaved-label="<?php echo esc_attr__('Unsaved changes', 'predictable-dialogs-ai-assistant'); ?>"
          data-saved-label="<?php echo esc_attr__('Saved', 'predictable-dialogs-ai-assistant'); ?>"
          aria-live="polite"
        ></span>
        <input
          type="submit"
          name="preddiai-save-settings"
          id="preddiai-save-settings"
          class="button button-primary"
          value="<?php echo esc_attr__('Save Settings', 'predictable-dialogs-ai-assistant'); ?>"
          disabled
        />
      </div>
    </div>
  </form>
</div>
