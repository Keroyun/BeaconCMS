/**
 * BeaconCMS Admin — JavaScript
 * ============================================================
 */

(function () {
  'use strict';

  /* ----------------------------------------------------------
     SIDEBAR TOGGLE (Collapse / Expand)
     ---------------------------------------------------------- */
  const sidebar = document.getElementById('adminSidebar');
  const collapseBtn = document.getElementById('sidebarCollapseBtn');
  const mobileOverlay = document.getElementById('mobileOverlay');
  const hamburgerBtn = document.getElementById('hamburgerBtn');

  function isMobile() {
    return window.innerWidth <= 768;
  }

  if (collapseBtn) {
    collapseBtn.addEventListener('click', function () {
      if (isMobile()) {
        sidebar.classList.remove('mobile-open');
        if (mobileOverlay) mobileOverlay.classList.remove('active');
      } else {
        sidebar.classList.toggle('collapsed');
        document.body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('beaconcms_sidebar', sidebar.classList.contains('collapsed') ? 'collapsed' : 'expanded');
      }
    });
  }

  // Restore sidebar state from localStorage
  if (!isMobile()) {
    const savedState = localStorage.getItem('beaconcms_sidebar');
    if (savedState === 'collapsed' && sidebar) {
      sidebar.classList.add('collapsed');
      document.body.classList.add('sidebar-collapsed');
    }
  }

  /* ----------------------------------------------------------
     MOBILE HAMBURGER MENU
     ---------------------------------------------------------- */
  if (hamburgerBtn) {
    hamburgerBtn.addEventListener('click', function () {
      sidebar.classList.toggle('mobile-open');
      if (mobileOverlay) mobileOverlay.classList.toggle('active');
    });
  }

  if (mobileOverlay) {
    mobileOverlay.addEventListener('click', function () {
      sidebar.classList.remove('mobile-open');
      mobileOverlay.classList.remove('active');
    });
  }

  // Handle resize events
  let resizeTimer;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      if (!isMobile()) {
        sidebar.classList.remove('mobile-open');
        if (mobileOverlay) mobileOverlay.classList.remove('active');
      }
    }, 200);
  });

  /* ----------------------------------------------------------
     USER DROPDOWN
     ---------------------------------------------------------- */
  const userBtn = document.getElementById('userDropdownBtn');
  const userDropdown = document.getElementById('userDropdown');

  if (userBtn && userDropdown) {
    userBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      userDropdown.classList.toggle('open');
    });

    document.addEventListener('click', function (e) {
      if (!userDropdown.contains(e.target)) {
        userDropdown.classList.remove('open');
      }
    });
  }

  /* ----------------------------------------------------------
     FLASH MESSAGE AUTO-DISMISS
     ---------------------------------------------------------- */
  function initFlashMessages() {
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(function (msg) {
      // Auto-dismiss after 4 seconds
      const timer = setTimeout(function () {
        dismissFlash(msg);
      }, 4000);

      // Click to dismiss immediately
      msg.addEventListener('click', function () {
        clearTimeout(timer);
        dismissFlash(msg);
      });
    });
  }

  function dismissFlash(el) {
    el.classList.add('flash-out');
    setTimeout(function () {
      el.remove();
    }, 400);
  }

  initFlashMessages();

  /* ----------------------------------------------------------
     AUTO-SLUG GENERATION
     ---------------------------------------------------------- */
  const titleField = document.getElementById('inputTitle');
  const slugField = document.getElementById('inputSlug');
  let slugManuallyEdited = false;

  function generateSlug(text) {
    return text
      .toString()
      .toLowerCase()
      .trim()
      .replace(/&/g, '-and-')
      .replace(/[\s\W-]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  if (titleField && slugField) {
    // Check if slug was already set (edit mode)
    if (slugField.value.trim() !== '') {
      slugManuallyEdited = true;
    }

    titleField.addEventListener('input', function () {
      if (!slugManuallyEdited) {
        slugField.value = generateSlug(titleField.value);
      }
    });

    slugField.addEventListener('input', function () {
      slugManuallyEdited = slugField.value.trim() !== '';
    });

    // Allow re-generating slug by clearing slug field
    slugField.addEventListener('blur', function () {
      if (slugField.value.trim() === '' && titleField.value.trim() !== '') {
        slugManuallyEdited = false;
        slugField.value = generateSlug(titleField.value);
      }
    });
  }

  /* ----------------------------------------------------------
     DELETE CONFIRMATION MODAL
     ---------------------------------------------------------- */
  const deleteModal = document.getElementById('deleteModal');
  const deleteForm = document.getElementById('deleteForm');
  const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
  const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

  function openDeleteModal(actionUrl, itemName) {
    if (!deleteModal) return;
    deleteModal.classList.add('active');
    if (deleteForm) deleteForm.action = actionUrl;
    const nameEl = document.getElementById('deleteItemName');
    if (nameEl) nameEl.textContent = itemName || 'this item';
  }

  function closeDeleteModal() {
    if (deleteModal) deleteModal.classList.remove('active');
  }

  if (cancelDeleteBtn) {
    cancelDeleteBtn.addEventListener('click', closeDeleteModal);
  }

  if (deleteModal) {
    deleteModal.addEventListener('click', function (e) {
      if (e.target === deleteModal) closeDeleteModal();
    });
  }

  // Attach to all delete buttons
  document.querySelectorAll('[data-delete-url]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      openDeleteModal(
        btn.getAttribute('data-delete-url'),
        btn.getAttribute('data-delete-name')
      );
    });
  });

  // Make openDeleteModal globally accessible
  window.openDeleteModal = openDeleteModal;
  window.closeDeleteModal = closeDeleteModal;

  /* ----------------------------------------------------------
     AJAX MEDIA UPLOAD (Drag & Drop + Click)
     ---------------------------------------------------------- */
  const uploadZone = document.getElementById('uploadZone');
  const uploadInput = document.getElementById('uploadFileInput');
  const uploadProgress = document.getElementById('uploadProgress');
  const progressBarFill = document.getElementById('progressBarFill');
  const progressText = document.getElementById('progressText');

  if (uploadZone) {
    // Click to browse
    uploadZone.addEventListener('click', function () {
      if (uploadInput) uploadInput.click();
    });

    // Drag events
    ['dragenter', 'dragover'].forEach(function (eventName) {
      uploadZone.addEventListener(eventName, function (e) {
        e.preventDefault();
        e.stopPropagation();
        uploadZone.classList.add('dragover');
      });
    });

    ['dragleave', 'drop'].forEach(function (eventName) {
      uploadZone.addEventListener(eventName, function (e) {
        e.preventDefault();
        e.stopPropagation();
        uploadZone.classList.remove('dragover');
      });
    });

    uploadZone.addEventListener('drop', function (e) {
      var files = e.dataTransfer.files;
      if (files.length > 0) {
        handleFileUpload(files);
      }
    });

    if (uploadInput) {
      uploadInput.addEventListener('change', function () {
        if (uploadInput.files.length > 0) {
          handleFileUpload(uploadInput.files);
        }
      });
    }
  }

  function handleFileUpload(files) {
    var formData = new FormData();
    for (var i = 0; i < files.length; i++) {
      formData.append('files[]', files[i]);
    }

    // Get CSRF token
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta) {
      formData.append('csrf_token', csrfMeta.getAttribute('content'));
    }

    if (uploadProgress) uploadProgress.classList.add('active');

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/admin/media/upload', true);

    xhr.upload.addEventListener('progress', function (e) {
      if (e.lengthComputable) {
        var percent = Math.round((e.loaded / e.total) * 100);
        if (progressBarFill) progressBarFill.style.width = percent + '%';
        if (progressText) progressText.textContent = 'Uploading... ' + percent + '%';
      }
    });

    xhr.addEventListener('load', function () {
      if (xhr.status >= 200 && xhr.status < 300) {
        if (progressText) progressText.textContent = 'Upload complete!';
        setTimeout(function () {
          location.reload();
        }, 800);
      } else {
        if (progressText) {
          progressText.textContent = 'Upload failed. Please try again.';
          progressText.style.color = '#ef4444';
        }
      }
    });

    xhr.addEventListener('error', function () {
      if (progressText) {
        progressText.textContent = 'Upload failed. Please try again.';
        progressText.style.color = '#ef4444';
      }
    });

    xhr.send(formData);
  }

  /* ----------------------------------------------------------
     SEO SECTION TOGGLE
     ---------------------------------------------------------- */
  document.querySelectorAll('.seo-section-toggle').forEach(function (toggle) {
    toggle.addEventListener('click', function () {
      var section = toggle.closest('.seo-section');
      if (section) section.classList.toggle('open');
    });
  });

  /* ----------------------------------------------------------
     GOOGLE SEARCH PREVIEW (Live Update)
     ---------------------------------------------------------- */
  var seoTitle = document.getElementById('inputSeoTitle');
  var seoDesc = document.getElementById('inputMetaDescription');
  var previewTitle = document.getElementById('googlePreviewTitle');
  var previewDesc = document.getElementById('googlePreviewDesc');

  function updateGooglePreview() {
    if (previewTitle) {
      var titleVal = seoTitle && seoTitle.value.trim() !== ''
        ? seoTitle.value
        : (titleField ? titleField.value : 'Page Title');
      previewTitle.textContent = titleVal || 'Page Title';
    }
    if (previewDesc) {
      previewDesc.textContent = (seoDesc && seoDesc.value.trim() !== '')
        ? seoDesc.value
        : 'Add a meta description to preview how this page appears in search results.';
    }
  }

  if (seoTitle) seoTitle.addEventListener('input', updateGooglePreview);
  if (seoDesc) seoDesc.addEventListener('input', updateGooglePreview);
  if (titleField) titleField.addEventListener('input', updateGooglePreview);
  updateGooglePreview();

  /* ----------------------------------------------------------
     CHARACTER COUNTERS (SEO Title & Meta Description)
     ---------------------------------------------------------- */
  function initCharCounter(inputId, counterId, maxLen) {
    var input = document.getElementById(inputId);
    var counter = document.getElementById(counterId);
    if (!input || !counter) return;

    function update() {
      var len = input.value.length;
      counter.textContent = len + '/' + maxLen;
      if (len > maxLen) {
        counter.classList.add('over-limit');
      } else {
        counter.classList.remove('over-limit');
      }
    }

    input.addEventListener('input', update);
    update();
  }

  initCharCounter('inputSeoTitle', 'seoTitleCounter', 60);
  initCharCounter('inputMetaDescription', 'metaDescCounter', 160);

  /* ----------------------------------------------------------
     TINYMCE INITIALIZATION
     ---------------------------------------------------------- */
  function initTinyMCE() {
    if (typeof tinymce === 'undefined') return;

    var editors = document.querySelectorAll('.wysiwyg-editor');
    if (editors.length === 0) return;

    tinymce.init({
      selector: '.wysiwyg-editor',
      height: 400,
      menubar: false,
      skin: 'oxide-dark',
      content_css: 'dark',
      plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
        'preview', 'anchor', 'searchreplace', 'visualblocks', 'code',
        'fullscreen', 'insertdatetime', 'media', 'table', 'help', 'wordcount'
      ],
      toolbar: 'undo redo | blocks | bold italic forecolor | ' +
        'alignleft aligncenter alignright alignjustify | ' +
        'bullist numlist outdent indent | link image media | ' +
        'removeformat | code fullscreen | help',
      content_style: 'body { font-family: Inter, sans-serif; font-size: 15px; color: #e2e8f0; background: #181b25; }',
      branding: false,
      promotion: false,
      setup: function (editor) {
        editor.on('change', function () {
          editor.save();
          formChanged = true;
        });
      }
    });
  }

  // Wait for TinyMCE script to load, then init
  if (document.querySelector('.wysiwyg-editor')) {
    if (typeof tinymce !== 'undefined') {
      initTinyMCE();
    } else {
      // Load TinyMCE from CDN
      var script = document.createElement('script');
      script.src = 'https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js';
      script.referrerPolicy = 'origin';
      script.onload = initTinyMCE;
      document.head.appendChild(script);
    }
  }

  /* ----------------------------------------------------------
     FORM UNSAVED CHANGES WARNING
     ---------------------------------------------------------- */
  var formChanged = false;
  var adminForms = document.querySelectorAll('form[data-warn-unsaved]');

  adminForms.forEach(function (form) {
    form.addEventListener('input', function () {
      formChanged = true;
    });
    form.addEventListener('change', function () {
      formChanged = true;
    });
    form.addEventListener('submit', function () {
      formChanged = false;
    });
  });

  window.addEventListener('beforeunload', function (e) {
    if (formChanged) {
      e.preventDefault();
      e.returnValue = '';
    }
  });

  /* ----------------------------------------------------------
     IMAGE PREVIEW ON FILE SELECT
     ---------------------------------------------------------- */
  document.querySelectorAll('[data-image-preview]').forEach(function (input) {
    input.addEventListener('change', function () {
      var previewId = input.getAttribute('data-image-preview');
      var preview = document.getElementById(previewId);
      if (!preview) return;

      if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
          // If preview is an img tag
          if (preview.tagName === 'IMG') {
            preview.src = e.target.result;
            preview.style.display = 'block';
          } else {
            // If preview is a container
            preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
          }
          var placeholder = preview.closest('.file-upload-preview');
          if (placeholder) {
            var icon = placeholder.querySelector('.placeholder-icon');
            if (icon) icon.style.display = 'none';
          }
        };
        reader.readAsDataURL(input.files[0]);
      }
    });
  });

  /* ----------------------------------------------------------
     SETTINGS TABS
     ---------------------------------------------------------- */
  var settingsTabs = document.querySelectorAll('.settings-tab');
  var settingsPanels = document.querySelectorAll('.settings-panel');

  settingsTabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      var target = tab.getAttribute('data-tab');

      settingsTabs.forEach(function (t) { t.classList.remove('active'); });
      settingsPanels.forEach(function (p) { p.classList.remove('active'); });

      tab.classList.add('active');
      var panel = document.getElementById('panel-' + target);
      if (panel) panel.classList.add('active');
    });
  });

  /* ----------------------------------------------------------
     TABLE SORTING
     ---------------------------------------------------------- */
  document.querySelectorAll('.data-table th.sortable').forEach(function (th) {
    th.addEventListener('click', function () {
      var table = th.closest('table');
      var tbody = table.querySelector('tbody');
      var colIndex = Array.from(th.parentNode.children).indexOf(th);
      var rows = Array.from(tbody.querySelectorAll('tr'));
      var isAsc = th.classList.contains('sort-asc');

      // Reset all sort indicators
      table.querySelectorAll('th').forEach(function (header) {
        header.classList.remove('sort-asc', 'sort-desc');
      });

      // Toggle sort direction
      th.classList.add(isAsc ? 'sort-desc' : 'sort-asc');
      var direction = isAsc ? -1 : 1;

      rows.sort(function (a, b) {
        var aVal = a.children[colIndex] ? a.children[colIndex].textContent.trim().toLowerCase() : '';
        var bVal = b.children[colIndex] ? b.children[colIndex].textContent.trim().toLowerCase() : '';

        // Try numeric sort first
        var aNum = parseFloat(aVal);
        var bNum = parseFloat(bVal);
        if (!isNaN(aNum) && !isNaN(bNum)) {
          return (aNum - bNum) * direction;
        }

        // String sort
        return aVal.localeCompare(bVal) * direction;
      });

      rows.forEach(function (row) {
        tbody.appendChild(row);
      });
    });
  });

  /* ----------------------------------------------------------
     DATE PICKER INITIALIZATION
     ---------------------------------------------------------- */
  // Using native date inputs with dark theme (handled via CSS)
  // No additional JS needed for basic date picker

  /* ----------------------------------------------------------
     RESPONSIVE SIDEBAR HANDLING
     ---------------------------------------------------------- */
  // Close sidebar on nav link click (mobile)
  document.querySelectorAll('.sidebar-nav a').forEach(function (link) {
    link.addEventListener('click', function () {
      if (isMobile() && sidebar) {
        sidebar.classList.remove('mobile-open');
        if (mobileOverlay) mobileOverlay.classList.remove('active');
      }
    });
  });

  /* ----------------------------------------------------------
     KEYBOARD SHORTCUTS
     ---------------------------------------------------------- */
  document.addEventListener('keydown', function (e) {
    // Escape closes modal
    if (e.key === 'Escape') {
      closeDeleteModal();
      if (userDropdown) userDropdown.classList.remove('open');
    }
  });

})();
