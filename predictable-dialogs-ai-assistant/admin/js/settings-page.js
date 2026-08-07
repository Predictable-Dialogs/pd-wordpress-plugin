(function () {
  var disableWidgetCheckbox = document.getElementById('preddiai-disable-widget');
  var disableWidgetStatus = document.getElementById('preddiai-disable-widget-status');

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

  var snippetField = document.getElementById('preddiai-snippet');
  var saveButton = document.getElementById('preddiai-save-settings')
    || document.querySelector('input[type="submit"][name="preddiai-save-settings"]')
    || document.querySelector('.preddiai-settings-form .button-primary');

  if (!snippetField || !saveButton) {
    return;
  }

  var initialSnippetValue = snippetField.value;
  var updateSaveHighlight = function () {
    var isSnippetChanged = snippetField.value !== initialSnippetValue;
    saveButton.classList.toggle('preddiai-save-highlight', isSnippetChanged);
  };

  snippetField.addEventListener('input', updateSaveHighlight);
  updateSaveHighlight();
})();
