<?php

if (!defined('ABSPATH')) {
    exit;
}

$option_name = PD_WP_Settings::OPTION_NAME;
$snippet_placeholder = 'Paste the WordPress initialization snippet here.';
?>
<style>
  .pd-settings-shell {
    max-width: 900px;
  }
  .pd-settings-shell > p {
    max-width: 860px;
    margin-bottom: 16px;
  }
  .pd-settings-form .form-table {
    margin-top: 8px;
  }
  .pd-settings-form .form-table > tbody > tr > th,
  .pd-settings-form .form-table > tbody > tr > td {
    padding-top: 14px;
    padding-bottom: 14px;
    vertical-align: top;
  }
  .pd-settings-form .form-table > tbody > tr + tr > th,
  .pd-settings-form .form-table > tbody > tr + tr > td {
    border-top: 1px solid #f0f0f1;
  }
  .pd-settings-form .description {
    margin-top: 8px;
  }
  .pd-settings-form .submit {
    margin-top: 16px;
  }
  .pd-settings-form .button-primary {
    transition: box-shadow 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
  }
  .pd-settings-form .button-primary.pd-save-highlight {
    background: #d63638;
    border-color: #b32d2e;
    box-shadow: 0 0 0 3px rgba(214, 54, 56, 0.2);
  }
  .pd-settings-form .button-primary.pd-save-highlight:hover,
  .pd-settings-form .button-primary.pd-save-highlight:focus {
    background: #b32d2e;
    border-color: #8a2424;
  }
  .pd-code-textarea {
    min-height: 220px;
    font-family: Consolas, Monaco, Menlo, monospace;
    font-size: 13px;
    line-height: 1.45;
    background: #f6f7f7;
  }
  .pd-toggle-control {
    display: inline-flex;
    align-items: center;
    gap: 10px;
  }
  .pd-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
  }
  .pd-switch input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
  }
  .pd-switch-slider {
    position: absolute;
    inset: 0;
    border-radius: 999px;
    background: #a7aaad;
    transition: background-color 0.2s ease;
    cursor: pointer;
  }
  .pd-switch-slider::before {
    content: "";
    position: absolute;
    top: 3px;
    left: 3px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    transition: transform 0.2s ease;
  }
  .pd-switch input:checked + .pd-switch-slider {
    background: #d63638;
  }
  .pd-switch input:checked + .pd-switch-slider::before {
    transform: translateX(20px);
  }
  .pd-switch input:focus-visible + .pd-switch-slider {
    outline: 2px solid #d63638;
    outline-offset: 2px;
  }
  .pd-toggle-status {
    font-weight: 600;
    min-width: 72px;
  }
  .pd-toggle-status.is-disabled {
    color: #b32d2e;
  }
</style>
<div class="wrap pd-settings-shell">
  <h1><?php esc_html_e('AI Chatbot Settings', 'pd-ai-chatbot'); ?></h1>
  <p>
    <?php esc_html_e('Create your chatbot in Predictable Dialogs first, then copy the WordPress snippet here. Use this for Bubble and Popup widgets. For Standard Widget, use shortcode directly on the page.', 'pd-ai-chatbot'); ?>
  </p>

  <form method="post" action="options.php" class="pd-settings-form">
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
              rows="9"
              cols="80"
              class="large-text code pd-code-textarea"
              spellcheck="false"
              autocapitalize="off"
              autocorrect="off"
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
              <?php esc_html_e('Pass logged-in user data to the widget as PD.user', 'pd-ai-chatbot'); ?>
            </label>
            <p class="description">
              <?php esc_html_e('Includes standard WordPress user fields.', 'pd-ai-chatbot'); ?>
            </p>
          </td>
        </tr>

        <tr>
          <th scope="row">
            <label for="pd-disable-widget"><?php esc_html_e('Disable widget', 'pd-ai-chatbot'); ?></label>
          </th>
          <td>
            <div class="pd-toggle-control">
              <label class="pd-switch" for="pd-disable-widget">
                <input
                  type="checkbox"
                  id="pd-disable-widget"
                  name="<?php echo esc_attr($option_name); ?>[disable_widget]"
                  value="1"
                  <?php checked(!empty($settings['disable_widget'])); ?>
                />
                <span class="pd-switch-slider" aria-hidden="true"></span>
              </label>
              <span
                id="pd-disable-widget-status"
                class="pd-toggle-status"
                data-disabled-label="<?php echo esc_attr__('Disabled', 'pd-ai-chatbot'); ?>"
              ></span>
            </div>
            <p class="description">
              <?php esc_html_e('Disables the widget from showing.', 'pd-ai-chatbot'); ?>
            </p>
          </td>
        </tr>

      </tbody>
    </table>

    <?php submit_button(__('Save Settings', 'pd-ai-chatbot'), 'primary', 'pd-save-settings', true, 'id="pd-save-settings"'); ?>
  </form>
</div>
<script>
  (function () {
    var disableWidgetCheckbox = document.getElementById('pd-disable-widget');
    var disableWidgetStatus = document.getElementById('pd-disable-widget-status');
    if (disableWidgetCheckbox && disableWidgetStatus) {
      var disabledLabel = disableWidgetStatus.dataset.disabledLabel || 'Disabled';

      var updateDisableWidgetStatus = function () {
        var isDisabled = disableWidgetCheckbox.checked;
        disableWidgetStatus.textContent = isDisabled ? disabledLabel : '';
        disableWidgetStatus.hidden = !isDisabled;
        disableWidgetStatus.classList.toggle('is-disabled', isDisabled);
      };

      disableWidgetCheckbox.addEventListener('change', updateDisableWidgetStatus);
      updateDisableWidgetStatus();
    }

    var snippetField = document.getElementById('pd-snippet');
    var saveButton = document.getElementById('pd-save-settings')
      || document.querySelector('input[type="submit"][name="pd-save-settings"]')
      || document.querySelector('.pd-settings-form .button-primary');
    if (!snippetField || !saveButton) return;

    var initialSnippetValue = snippetField.value;
    var updateSaveHighlight = function () {
      var isSnippetChanged = snippetField.value !== initialSnippetValue;
      saveButton.classList.toggle('pd-save-highlight', isSnippetChanged);
    };

    snippetField.addEventListener('input', updateSaveHighlight);
    updateSaveHighlight();
  })();
</script>
