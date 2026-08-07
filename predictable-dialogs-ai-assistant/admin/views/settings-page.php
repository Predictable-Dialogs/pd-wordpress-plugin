<?php

if (!defined('ABSPATH')) {
    exit;
}

$option_name = PREDDIAI_Settings::OPTION_NAME;
$snippet_placeholder = 'Paste the WordPress initialization snippet here.';
?>
<div class="wrap preddiai-settings-shell">
  <h1><?php esc_html_e('Predictable Dialogs AI Assistant Settings', 'predictable-dialogs-ai-assistant'); ?></h1>
  <p>
    <?php esc_html_e('Create your chatbot in Predictable Dialogs first, then copy the WordPress snippet here. Use this for Bubble and Popup widgets. For Standard Widget, use shortcode directly on the page.', 'predictable-dialogs-ai-assistant'); ?>
  </p>

  <form method="post" action="options.php" class="preddiai-settings-form">
    <?php settings_fields(PREDDIAI_Settings::SETTINGS_GROUP); ?>

    <table class="form-table" role="presentation">
      <tbody>
        <tr>
          <th scope="row">
            <label for="preddiai-snippet"><?php esc_html_e('Initialization snippet', 'predictable-dialogs-ai-assistant'); ?></label>
          </th>
          <td>
            <textarea
              id="preddiai-snippet"
              name="<?php echo esc_attr($option_name); ?>[initialization_snippet]"
              rows="9"
              cols="80"
              class="large-text code preddiai-code-textarea"
              spellcheck="false"
              autocapitalize="off"
              autocorrect="off"
              placeholder="<?php echo esc_attr($snippet_placeholder); ?>"
            ><?php echo esc_textarea($settings['initialization_snippet']); ?></textarea>
            <p class="description">
              <?php esc_html_e('Paste the WordPress snippet generated in Predictable Dialogs Install for Bubble or Popup.', 'predictable-dialogs-ai-assistant'); ?>
            </p>
          </td>
        </tr>

        <tr>
          <th scope="row">
            <label for="preddiai-excluded-pages"><?php esc_html_e('Excluded pages', 'predictable-dialogs-ai-assistant'); ?></label>
          </th>
          <td>
            <input
              type="text"
              id="preddiai-excluded-pages"
              name="<?php echo esc_attr($option_name); ?>[excluded_pages]"
              class="regular-text"
              value="<?php echo esc_attr($settings['excluded_pages']); ?>"
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
          </td>
        </tr>

        <tr>
          <th scope="row">
            <label for="preddiai-include-user"><?php esc_html_e('Inject logged-in WordPress user', 'predictable-dialogs-ai-assistant'); ?></label>
          </th>
          <td>
            <label>
              <input
                type="checkbox"
                id="preddiai-include-user"
                name="<?php echo esc_attr($option_name); ?>[include_logged_in_user]"
                value="1"
                <?php checked(!empty($settings['include_logged_in_user'])); ?>
              />
              <?php esc_html_e('Pass logged-in user data to the widget as Preddiai.user', 'predictable-dialogs-ai-assistant'); ?>
            </label>
            <p class="description">
              <?php esc_html_e('Includes standard WordPress user fields.', 'predictable-dialogs-ai-assistant'); ?>
            </p>
          </td>
        </tr>

        <tr>
          <th scope="row">
            <label for="preddiai-disable-widget"><?php esc_html_e('Disable widget', 'predictable-dialogs-ai-assistant'); ?></label>
          </th>
          <td>
            <div class="preddiai-toggle-control">
              <label class="preddiai-switch" for="preddiai-disable-widget">
                <input
                  type="checkbox"
                  id="preddiai-disable-widget"
                  name="<?php echo esc_attr($option_name); ?>[disable_widget]"
                  value="1"
                  <?php checked(!empty($settings['disable_widget'])); ?>
                />
                <span class="preddiai-switch-slider" aria-hidden="true"></span>
              </label>
              <span
                id="preddiai-disable-widget-status"
                class="preddiai-toggle-status"
                data-disabled-label="<?php echo esc_attr__('Disabled', 'predictable-dialogs-ai-assistant'); ?>"
              ></span>
            </div>
            <p class="description">
              <?php esc_html_e('Disables the widget from showing.', 'predictable-dialogs-ai-assistant'); ?>
            </p>
          </td>
        </tr>

      </tbody>
    </table>

    <?php submit_button(__('Save Settings', 'predictable-dialogs-ai-assistant'), 'primary', 'preddiai-save-settings', true, 'id="preddiai-save-settings"'); ?>
  </form>
</div>
