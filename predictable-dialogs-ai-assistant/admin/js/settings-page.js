(function () {
  var form = document.querySelector('.preddiai-settings-form');
  var saveBar = document.querySelector('[data-save-bar]');
  var saveStatus = document.querySelector('[data-save-status]');
  var saveButton = document.getElementById('preddiai-save-settings')
    || document.querySelector('input[type="submit"][name="preddiai-save-settings"]')
    || document.querySelector('.preddiai-settings-form .button-primary');
  var savedStateTimer = null;

  var serializeForm = function () {
    if (!form) {
      return '';
    }

    return Array.prototype.slice.call(form.elements)
      .filter(function (element) {
        return element.name
          && !element.disabled
          && element.type !== 'submit'
          && element.type !== 'button'
          && element.type !== 'reset';
      })
      .map(function (element) {
        if ((element.type === 'checkbox' || element.type === 'radio') && !element.checked) {
          return element.name + '=';
        }
        return element.name + '=' + element.value;
      })
      .join('&');
  };

  var initialFormValue = serializeForm();
  var setSaveBarState = function (state) {
    if (!saveBar || !saveButton) {
      return;
    }

    var isClean = state === 'clean';
    var isSaved = state === 'saved';
    saveBar.hidden = isClean;
    saveBar.classList.toggle('is-dirty', state === 'dirty');
    saveBar.classList.toggle('is-saved', isSaved);
    saveButton.disabled = state !== 'dirty';

    if (saveStatus) {
      if (state === 'dirty') {
        saveStatus.textContent = saveStatus.dataset.unsavedLabel || 'Unsaved changes';
      } else if (isSaved) {
        saveStatus.textContent = saveStatus.dataset.savedLabel || 'Saved';
      } else {
        saveStatus.textContent = '';
      }
    }
  };

  var clearSavedStateTimer = function () {
    if (!savedStateTimer) {
      return;
    }
    window.clearTimeout(savedStateTimer);
    savedStateTimer = null;
  };

  var updateSaveBar = function () {
    clearSavedStateTimer();
    setSaveBarState(serializeForm() !== initialFormValue ? 'dirty' : 'clean');
  };

  var showSavedState = function () {
    clearSavedStateTimer();
    setSaveBarState('saved');
    savedStateTimer = window.setTimeout(function () {
      savedStateTimer = null;
      updateSaveBar();
    }, 1800);
  };

  if (form) {
    form.addEventListener('input', updateSaveBar);
    form.addEventListener('change', updateSaveBar);
    form.addEventListener('submit', function (event) {
      if (serializeForm() === initialFormValue) {
        event.preventDefault();
        return;
      }

      if (saveButton) {
        saveButton.disabled = true;
      }
    });
  }

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

  var widgetTypeSelect = document.getElementById('preddiai-widget-type');
  var widgetPanels = document.querySelectorAll('[data-widget-panel]');
  var widgetCustomizationTitle = document.querySelector('[data-widget-customization-title]');

  var updateWidgetPanel = function () {
    var widgetType = widgetTypeSelect ? widgetTypeSelect.value : 'Standard';
    widgetPanels.forEach(function (panel) {
      panel.classList.toggle('is-active', panel.dataset.widgetPanel === widgetType);
    });

    if (widgetCustomizationTitle) {
      var labels = {
        Standard: widgetCustomizationTitle.dataset.standardTitle || 'Customize Standard Widget',
        Bubble: widgetCustomizationTitle.dataset.bubbleTitle || 'Customize Bubble Widget',
        Popup: widgetCustomizationTitle.dataset.popupTitle || 'Customize Popup Widget',
      };
      widgetCustomizationTitle.textContent = labels[widgetType] || labels.Standard;
    }
  };

  if (widgetTypeSelect) {
    widgetTypeSelect.addEventListener('change', updateWidgetPanel);
    updateWidgetPanel();
  }

  var standardPlacementMode = document.getElementById('preddiai-standard-placement-mode');
  var standardShortcodeField = document.querySelector('[data-standard-shortcode-field]');
  var standardPageField = document.querySelector('[data-standard-page-field]');
  var standardContentPositionField = document.querySelector('[data-standard-content-position-field]');

  var updateStandardPlacementFields = function () {
    if (!standardPlacementMode) {
      return;
    }

    if (standardShortcodeField) {
      standardShortcodeField.hidden = standardPlacementMode.value !== 'manual';
    }

    if (standardPageField) {
      standardPageField.hidden = standardPlacementMode.value !== 'selected_page';
    }

    if (standardContentPositionField) {
      standardContentPositionField.hidden = standardPlacementMode.value === 'manual';
    }
  };

  if (standardPlacementMode) {
    standardPlacementMode.addEventListener('change', updateStandardPlacementFields);
    updateStandardPlacementFields();
  }

  var standardShortcode = document.querySelector('[data-standard-shortcode]');
  var standardWidthInput = document.querySelector('[data-standard-shortcode-width]');
  var standardHeightInput = document.querySelector('[data-standard-shortcode-height]');

  var shortcodeValue = function (input, fallback) {
    var value = input ? String(input.value || '').trim() : '';
    return value || fallback;
  };

  var updateStandardShortcode = function () {
    if (!standardShortcode) {
      return;
    }

    var agent = standardShortcode.dataset.shortcodeAgent || '';
    var width = shortcodeValue(standardWidthInput, '100%');
    var height = shortcodeValue(standardHeightInput, '600px');
    standardShortcode.value = '[preddiai agent="' + agent + '" width="' + width + '" height="' + height + '"]';
  };

  [standardWidthInput, standardHeightInput].forEach(function (input) {
    if (!input) {
      return;
    }

    input.addEventListener('input', updateStandardShortcode);
    input.addEventListener('change', updateStandardShortcode);
  });

  document.querySelectorAll('[data-copy-target]').forEach(function (button) {
    button.addEventListener('click', function () {
      var target = document.getElementById(button.dataset.copyTarget || '');
      if (!target || target.disabled) {
        return;
      }

      target.focus();
      target.select();

      var showCopied = function () {
        var originalText = button.textContent.trim() || 'Copy';
        button.textContent = 'Copied';
        window.setTimeout(function () {
          button.textContent = originalText;
        }, 1600);
      };

      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(target.value).then(showCopied).catch(function () {
          if (document.execCommand('copy')) {
            showCopied();
          }
        });
        return;
      }

      if (document.execCommand('copy')) {
        showCopied();
      }
    });
  });

  updateStandardShortcode();

  var setupDelayToggle = function (checkboxId) {
    var checkbox = document.getElementById(checkboxId);
    var controls = document.querySelector('[data-toggle-source="' + checkboxId + '"]');
    if (!checkbox || !controls) {
      return;
    }

    var update = function () {
      controls.hidden = !checkbox.checked;
    };

    checkbox.addEventListener('change', update);
    update();
  };

  setupDelayToggle('preddiai-popup-auto-show');
  setupDelayToggle('preddiai-preview-auto-show');

  document.querySelectorAll('.preddiai-stepper-button').forEach(function (button) {
    button.addEventListener('click', function () {
      var target = document.getElementById(button.dataset.stepTarget);
      var step = parseInt(button.dataset.step || '0', 10);
      if (!target || !step) {
        return;
      }

      var current = parseInt(target.value || '1', 10);
      if (!Number.isFinite(current) || current < 1) {
        current = 1;
      }
      target.value = Math.max(1, current + step);
      target.dispatchEvent(new Event('input', { bubbles: true }));
    });
  });

  var colorInput = document.getElementById('preddiai-bubble-button-color');
  var colorLabel = document.getElementById('preddiai-bubble-color-label');
  var colorSwatches = document.querySelectorAll('.preddiai-color-swatch');
  var bubbleIconPreview = document.querySelector('.preddiai-bubble-icon-preview');
  var previewButton = document.querySelector('.preddiai-preview-button');
  var customColorButton = document.querySelector('.preddiai-custom-color-button');

  var normalizeColor = function (value) {
    return String(value || '').trim().toLowerCase();
  };

  var iconColorForBackground = function (hexColor) {
    var hex = String(hexColor || '').replace('#', '').trim();
    if (hex.length === 3) {
      hex = hex.split('').map(function (value) {
        return value + value;
      }).join('');
    }

    var match = /^([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    if (!match) {
      return '#fff';
    }

    var red = parseInt(match[1], 16);
    var green = parseInt(match[2], 16);
    var blue = parseInt(match[3], 16);
    return ((red * 299 + green * 587 + blue * 114) / 1000) > 155 ? '#27272A' : '#fff';
  };

  var updateColor = function (color) {
    if (!colorInput || !color) {
      return;
    }

    colorInput.value = color;
    if (colorLabel) {
      colorLabel.textContent = String(color).toUpperCase();
    }
    if (customColorButton) {
      customColorButton.style.backgroundColor = color;
    }
    if (bubbleIconPreview) {
      bubbleIconPreview.style.backgroundColor = color;
      bubbleIconPreview.style.color = iconColorForBackground(color);
    }
    if (previewButton) {
      previewButton.style.backgroundColor = color;
      previewButton.style.color = iconColorForBackground(color);
    }

    var normalized = normalizeColor(color);
    colorSwatches.forEach(function (swatch) {
      swatch.classList.toggle('is-active', normalizeColor(swatch.dataset.color) === normalized);
    });
  };

  colorSwatches.forEach(function (swatch) {
    swatch.addEventListener('click', function () {
      updateColor(swatch.dataset.color);
      if (colorInput) {
        colorInput.dispatchEvent(new Event('input', { bubbles: true }));
      }
    });
  });

  if (colorInput) {
    colorInput.addEventListener('input', function () {
      updateColor(colorInput.value);
    });
    updateColor(colorInput.value);
  }

  var previewStage = document.querySelector('.preddiai-preview-stage');
  var placementSelect = document.querySelector('select[name$="[bubble_placement]"]');
  var sizeSelect = document.querySelector('select[name$="[bubble_button_size]"]');
  var previewMessageCheckbox = document.getElementById('preddiai-preview-message-enabled');
  var previewMessageSettings = document.querySelector('.preddiai-preview-message-settings');
  var previewMessage = document.querySelector('.preddiai-preview-message');
  var previewMessageTextInput = document.getElementById('preddiai-preview-message-text');
  var previewAvatarInput = document.getElementById('preddiai-preview-message-avatar-url');
  var customIconInput = document.getElementById('preddiai-bubble-custom-icon-src');
  var bubbleDefaultIconTemplate = document.getElementById('preddiai-bubble-default-icon-template');

  var renderIcon = function (target, url) {
    if (!target) {
      return;
    }

    target.innerHTML = '';
    if (url) {
      var image = document.createElement('img');
      image.src = url;
      image.alt = '';
      image.style.width = '45%';
      image.style.height = '45%';
      image.style.objectFit = 'contain';
      target.appendChild(image);
      return;
    }

    if (bubbleDefaultIconTemplate && bubbleDefaultIconTemplate.content) {
      var icon = bubbleDefaultIconTemplate.content.firstElementChild;
      if (icon) {
        target.appendChild(icon.cloneNode(true));
      }
    }
  };

  var updatePreview = function () {
    if (previewStage) {
      previewStage.classList.toggle('is-left', placementSelect && placementSelect.value === 'left');
      previewStage.classList.toggle('is-large', sizeSelect && sizeSelect.value === 'large');
    }

    var isPreviewMessageEnabled = previewMessageCheckbox && previewMessageCheckbox.checked;
    if (previewMessageSettings) {
      previewMessageSettings.hidden = !isPreviewMessageEnabled;
    }
    if (previewMessage) {
      previewMessage.classList.toggle('is-visible', Boolean(isPreviewMessageEnabled));
      var textNode = previewMessage.querySelector('span');
      if (textNode && previewMessageTextInput) {
        textNode.textContent = previewMessageTextInput.value || 'Need help? Tap here to chat with us!';
      }
      var existingImage = previewMessage.querySelector('img');
      var avatarUrl = previewAvatarInput ? previewAvatarInput.value.trim() : '';
      if (avatarUrl && !existingImage) {
        existingImage = document.createElement('img');
        existingImage.alt = '';
        previewMessage.insertBefore(existingImage, previewMessage.firstChild);
      }
      if (existingImage) {
        if (avatarUrl) {
          existingImage.src = avatarUrl;
          existingImage.hidden = false;
        } else {
          existingImage.hidden = true;
        }
      }
    }

    var iconUrl = customIconInput ? customIconInput.value.trim() : '';
    renderIcon(bubbleIconPreview, iconUrl);
    renderIcon(previewButton, iconUrl);
  };

  [
    placementSelect,
    sizeSelect,
    previewMessageCheckbox,
    previewMessageTextInput,
    previewAvatarInput,
    customIconInput,
  ].forEach(function (element) {
    if (!element) {
      return;
    }
    element.addEventListener('input', updatePreview);
    element.addEventListener('change', updatePreview);
  });

  var snippetTextarea = document.getElementById('preddiai-snippet');
  var popupAutoShowCheckbox = document.getElementById('preddiai-popup-auto-show');
  var popupDelayInput = document.getElementById('preddiai-popup-delay');
  var previewAutoShowCheckbox = document.getElementById('preddiai-preview-auto-show');
  var previewDelayInput = document.getElementById('preddiai-preview-delay');

  var jsonString = function (value) {
    return JSON.stringify(String(value || ''));
  };

  var positiveIntValue = function (input, fallback) {
    var parsed = parseInt(input && input.value ? input.value : '', 10);
    return Number.isFinite(parsed) && parsed > 0 ? parsed : fallback;
  };

  var indentLines = function (value, prefix) {
    return String(value || '')
      .split('\n')
      .map(function (line) {
        return line ? prefix + line : line;
      })
      .join('\n');
  };

  var stripTrailingComma = function (value) {
    return String(value || '').trim().replace(/,\s*$/, '');
  };

  var findMatchingBrace = function (source, openIndex) {
    var depth = 0;
    var quote = '';
    var escaped = false;

    for (var index = openIndex; index < source.length; index++) {
      var char = source[index];

      if (quote) {
        if (escaped) {
          escaped = false;
        } else if (char === '\\') {
          escaped = true;
        } else if (char === quote) {
          quote = '';
        }
        continue;
      }

      if (char === '"' || char === "'" || char === '`') {
        quote = char;
        continue;
      }

      if (char === '{') {
        depth++;
      } else if (char === '}') {
        depth--;
        if (depth === 0) {
          return index;
        }
      }
    }

    return -1;
  };

  var extractPropertyBlock = function (source, property) {
    var pattern = new RegExp('\\b' + property + '\\s*:');
    var match = pattern.exec(source || '');
    if (!match) {
      return '';
    }

    var openIndex = source.indexOf('{', match.index);
    if (openIndex === -1) {
      return '';
    }

    var closeIndex = findMatchingBrace(source, openIndex);
    if (closeIndex === -1) {
      return '';
    }

    return stripTrailingComma(source.slice(match.index, closeIndex + 1));
  };

  var extractAgentName = function (source) {
    var match = /\bagentName\s*:\s*(["'])([\s\S]*?)\1/.exec(source || '');
    return match ? match[2].replace(/\\(["'\\])/g, '$1') : '';
  };

  var extractPreservedFields = function (source) {
    return [
      extractPropertyBlock(source, 'user'),
      extractPropertyBlock(source, 'contextVariables'),
      extractPropertyBlock(source, 'filterResponse'),
    ].filter(Boolean);
  };

  var optionObject = function (fields) {
    return fields
      .filter(Boolean)
      .map(function (field) {
        return indentLines(field, '  ');
      })
      .join(',\n');
  };

  var moduleSnippet = function (body, trailingMarkup) {
    var cdnUrl = snippetTextarea && snippetTextarea.dataset.embedCdnUrl
      ? snippetTextarea.dataset.embedCdnUrl
      : 'https://cdn.jsdelivr.net/npm/@agent-embed/js@latest/dist/web.js';

    return '<script type="module">\n'
      + '  import Agent from ' + jsonString(cdnUrl) + ';\n'
      + indentLines(body, '  ') + '\n'
      + '</script>'
      + (trailingMarkup ? '\n' + trailingMarkup : '');
  };

  var buildPopupSnippet = function (agentName, source) {
    var fields = ['agentName: ' + jsonString(agentName)];

    if (popupAutoShowCheckbox && popupAutoShowCheckbox.checked) {
      fields.push('autoShowDelay: ' + (positiveIntValue(popupDelayInput, 3) * 1000));
    }

    fields = fields.concat(extractPreservedFields(source));

    return moduleSnippet('Agent.initPopup({\n' + optionObject(fields) + '\n});');
  };

  var buildBubbleSnippet = function (agentName, source) {
    var fields = ['agentName: ' + jsonString(agentName)];

    if (previewMessageCheckbox && previewMessageCheckbox.checked) {
      var previewFields = [
        'message: ' + jsonString(previewMessageTextInput && previewMessageTextInput.value
          ? previewMessageTextInput.value
          : 'Need help? Tap here to chat with us!'),
      ];
      var avatarUrl = previewAvatarInput ? previewAvatarInput.value.trim() : '';
      if (avatarUrl) {
        previewFields.push('avatarUrl: ' + jsonString(avatarUrl));
      }
      if (previewAutoShowCheckbox && previewAutoShowCheckbox.checked) {
        previewFields.push('autoShowDelay: ' + (positiveIntValue(previewDelayInput, 3) * 1000));
      }

      fields.push('previewMessage: {\n' + optionObject(previewFields) + '\n}');
    }

    var buttonFields = [
      'size: ' + jsonString(sizeSelect && sizeSelect.value === 'large' ? 'large' : 'medium'),
      'backgroundColor: ' + jsonString(colorInput && colorInput.value ? colorInput.value : '#2b3e13'),
    ];
    var customIconUrl = customIconInput ? customIconInput.value.trim() : '';
    if (customIconUrl) {
      buttonFields.push('customIconSrc: ' + jsonString(customIconUrl));
    }

    fields.push('theme: {\n'
      + indentLines('placement: ' + jsonString(placementSelect && placementSelect.value === 'left' ? 'left' : 'right'), '  ')
      + ',\n'
      + indentLines('button: {\n' + optionObject(buttonFields) + '\n}', '  ')
      + '\n}');

    fields = fields.concat(extractPreservedFields(source));

    return moduleSnippet('Agent.initBubble({\n' + optionObject(fields) + '\n});');
  };

  var buildStandardSnippet = function (agentName, source) {
    var width = shortcodeValue(standardWidthInput, '100%');
    var height = shortcodeValue(standardHeightInput, '600px');
    var fields = ['agentName: ' + jsonString(agentName)].concat(extractPreservedFields(source));
    var standardElement = '<agent-standard style="width: ' + width + '; height: ' + height + ';"></agent-standard>';

    return moduleSnippet('Agent.initStandard({\n' + optionObject(fields) + '\n});', standardElement);
  };

  var updateInitializationSnippetFromControls = function () {
    if (!snippetTextarea || !widgetTypeSelect) {
      return;
    }

    var source = snippetTextarea.value || '';
    var agentName = extractAgentName(source) || snippetTextarea.dataset.agentName || '';
    if (!agentName) {
      return;
    }

    var widgetType = widgetTypeSelect.value || 'Standard';
    var nextSnippet = '';

    if (widgetType === 'Bubble') {
      nextSnippet = buildBubbleSnippet(agentName, source);
    } else if (widgetType === 'Popup') {
      nextSnippet = buildPopupSnippet(agentName, source);
    } else {
      nextSnippet = buildStandardSnippet(agentName, source);
    }

    if (nextSnippet && nextSnippet !== snippetTextarea.value) {
      snippetTextarea.value = nextSnippet;
      snippetTextarea.dispatchEvent(new Event('input', { bubbles: true }));
    }
  };

  [
    widgetTypeSelect,
    standardWidthInput,
    standardHeightInput,
    popupAutoShowCheckbox,
    popupDelayInput,
    placementSelect,
    sizeSelect,
    previewMessageCheckbox,
    previewMessageTextInput,
    previewAvatarInput,
    previewAutoShowCheckbox,
    previewDelayInput,
    colorInput,
    customIconInput,
  ].forEach(function (element) {
    if (!element) {
      return;
    }
    element.addEventListener('input', updateInitializationSnippetFromControls);
    element.addEventListener('change', updateInitializationSnippetFromControls);
  });

  updatePreview();
  if (saveBar && saveBar.dataset.savedOnLoad === '1') {
    showSavedState();
  } else {
    updateSaveBar();
  }
})();
