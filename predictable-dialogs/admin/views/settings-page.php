<?php

if (!defined('ABSPATH')) {
    exit;
}

$option_name = PD_WP_Settings::OPTION_NAME;
$snippet_placeholder = 'Paste the code from the Predictable Dialogs app here, e.g. Agent.initPopup({ agentName: "my-agent-name" }); or Agent.initBubble({ agentName: "my-agent-name" });';
?>
<div class="wrap">
  <h1><?php esc_html_e('AI Chatbot Settings', 'pd-ai-chatbot'); ?></h1>
  <p>
    <?php esc_html_e('Create your chatbot in Predictable Dialogs first, then copy the WordPress snippet here. Use this for Bubble and Popup widgets. For Standard Widget, use shortcode directly on the page.', 'pd-ai-chatbot'); ?>
  </p>

  <form method="post" action="options.php">
    <?php settings_fields(PD_WP_Settings::SETTINGS_GROUP); ?>

    <table class="form-table" role="presentation">
      <tbody>
        <tr>
          <th scope="row">
            <label for="pd-snippet"><?php esc_html_e('Initialization snippet', 'pd-ai-chatbot'); ?></label>
          </th>
          <td>
            <textarea
              id="pd-snippet"
              name="<?php echo esc_attr($option_name); ?>[initialization_snippet]"
              rows="14"
              cols="80"
              class="large-text code"
              placeholder="<?php echo esc_attr($snippet_placeholder); ?>"
            ><?php echo esc_textarea($settings['initialization_snippet']); ?></textarea>
            <p class="description">
              <?php esc_html_e('Paste the WordPress snippet generated in Predictable Dialogs Install for Bubble or Popup.', 'pd-ai-chatbot'); ?>
            </p>
          </td>
        </tr>

        <tr>
          <th scope="row">
            <label for="pd-excluded-pages"><?php esc_html_e('Excluded pages', 'pd-ai-chatbot'); ?></label>
          </th>
          <td>
            <input
              type="text"
              id="pd-excluded-pages"
              name="<?php echo esc_attr($option_name); ?>[excluded_pages]"
              class="regular-text"
              value="<?php echo esc_attr($settings['excluded_pages']); ?>"
              placeholder="/app/*, /app?param=*"
            />
            <p class="description">
              <?php esc_html_e('Comma-separated patterns. Supported examples:', 'pd-ai-chatbot'); ?>
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
            <label for="pd-include-user"><?php esc_html_e('Inject logged-in WordPress user', 'pd-ai-chatbot'); ?></label>
          </th>
          <td>
            <label>
              <input
                type="checkbox"
                id="pd-include-user"
                name="<?php echo esc_attr($option_name); ?>[include_logged_in_user]"
                value="1"
                <?php checked(!empty($settings['include_logged_in_user'])); ?>
              />
              <?php esc_html_e('Expose user data to snippet as PD.user (alias: PredictableDialogsWordPress.user)', 'pd-ai-chatbot'); ?>
            </label>
            <p class="description">
              <?php esc_html_e('Available user fields: user_id, user_name, user_email, user_segments', 'pd-ai-chatbot'); ?>
            </p>
          </td>
        </tr>

        <tr>
          <th scope="row">
            <label for="pd-disable-widget"><?php esc_html_e('Disable widget', 'pd-ai-chatbot'); ?></label>
          </th>
          <td>
            <label>
              <input
                type="checkbox"
                id="pd-disable-widget"
                name="<?php echo esc_attr($option_name); ?>[disable_widget]"
                value="1"
                <?php checked(!empty($settings['disable_widget'])); ?>
              />
              <?php esc_html_e('Disables the widget from showing', 'pd-ai-chatbot'); ?>
            </label>
          </td>
        </tr>

      </tbody>
    </table>

    <?php submit_button(__('Save Settings', 'pd-ai-chatbot')); ?>
  </form>
</div>
