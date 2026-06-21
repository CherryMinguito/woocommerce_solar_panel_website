(function () {
  'use strict';

  var restUrl = (window.jcsCalculator && window.jcsCalculator.restUrl) || '';
  var nonce = (window.jcsCalculator && window.jcsCalculator.nonce) || '';

  var uploadInput = document.getElementById('jcs-bill-upload');
  var uploadZone = document.getElementById('jcs-upload-zone');
  var ocrStatus = document.getElementById('jcs-ocr-status');
  var kwhInput = document.getElementById('jcs-monthly-kwh');
  var billInput = document.getElementById('jcs-monthly-bill');
  var calcBtn = document.getElementById('jcs-calculate-btn');
  var resultsEl = document.getElementById('jcs-calculator-results');

  if (!calcBtn) return;

  function showStatus(message, type) {
    if (!ocrStatus) return;
    ocrStatus.hidden = false;
    ocrStatus.textContent = message;
    ocrStatus.className = 'jcs-ocr-status jcs-ocr-status--' + type;
  }

  function formatCurrency(amount) {
    return '$' + Number(amount).toLocaleString();
  }

  function displayResults(data) {
    if (!resultsEl) return;
    resultsEl.hidden = false;

    document.getElementById('jcs-result-kw').textContent = data.system_kw + ' kW';
    document.getElementById('jcs-result-panels').textContent = data.panels;
    document.getElementById('jcs-result-cost').textContent = formatCurrency(data.estimated_cost);
    document.getElementById('jcs-result-savings').textContent = formatCurrency(data.annual_savings);
    document.getElementById('jcs-result-payback').textContent = data.payback_years + ' yrs';
    document.getElementById('jcs-result-monthly').textContent = formatCurrency(data.monthly_savings);

    resultsEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function calculate() {
    var kwh = parseFloat(kwhInput.value);
    var bill = billInput.value ? parseFloat(billInput.value) : null;

    if (!kwh || kwh <= 0) {
      showStatus('Please enter your monthly kWh usage.', 'error');
      return;
    }

    fetch(restUrl + 'calculate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': nonce
      },
      body: JSON.stringify({ monthly_kwh: kwh, monthly_bill: bill })
    })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data.error) {
          showStatus(data.error, 'error');
          return;
        }
        displayResults(data);
      })
      .catch(function () {
        showStatus('Calculation failed. Please try again.', 'error');
      });
  }

  function uploadBill(file) {
    if (!file) return;

    showStatus('Reading your bill...', 'loading');

    var formData = new FormData();
    formData.append('bill', file);

    fetch(restUrl + 'ocr', {
      method: 'POST',
      headers: { 'X-WP-Nonce': nonce },
      body: formData
    })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data.error) {
          showStatus(data.error, 'error');
          return;
        }

        var ocr = data.ocr || {};
        var confidence = ocr.confidence || 'none';

        if (ocr.kwh) {
          kwhInput.value = Math.round(ocr.kwh);
        }
        if (ocr.amount) {
          billInput.value = ocr.amount.toFixed(2);
        }

        if (confidence === 'high') {
          showStatus('Bill read successfully! Review the values below and click Calculate.', 'success');
        } else if (confidence === 'medium') {
          showStatus('Partial data extracted. Please verify the values below.', 'warning');
        } else {
          showStatus('Could not auto-read your bill. Please enter your usage manually.', 'warning');
        }

        if (data.estimate) {
          displayResults(data.estimate);
        }
      })
      .catch(function () {
        showStatus('Upload failed. Please enter your usage manually.', 'error');
      });
  }

  calcBtn.addEventListener('click', calculate);

  if (uploadInput) {
    uploadInput.addEventListener('change', function () {
      uploadBill(uploadInput.files[0]);
    });
  }

  if (uploadZone) {
    uploadZone.addEventListener('dragover', function (e) {
      e.preventDefault();
      uploadZone.classList.add('is-dragover');
    });

    uploadZone.addEventListener('dragleave', function () {
      uploadZone.classList.remove('is-dragover');
    });

    uploadZone.addEventListener('drop', function (e) {
      e.preventDefault();
      uploadZone.classList.remove('is-dragover');
      if (e.dataTransfer.files.length) {
        uploadBill(e.dataTransfer.files[0]);
      }
    });
  }
})();
