function openModal(id) {
  let modal = document.getElementById(id);

  modal.style.display = "block";

  setTimeout(function () {
    modal.style.transition = "0.5s";
    modal.style.opacity = "1";
  }, 0);
}

function closeModal(id) {
  let modal = document.getElementById(id);

  modal.style.transition = "0.5s";
  modal.style.opacity = "0";

  setTimeout(function () {
    modal.style.display = "none";
  }, 500);
}


function copy(text) {
  const textarea = document.createElement("textarea");
  textarea.value = text;
  document.body.appendChild(textarea);
  textarea.select();
  document.execCommand("copy");
  document.body.removeChild(textarea);
  return false;
}

function confirmModal(text, url) {
  if (!document.getElementById('confirm')) {
    const modalHtml = `
      <div id="confirm" class="w3-modal" style="z-index: 200; opacity: 0; display: none">
        <div class="w3-modal-content w3-card-4">
          <div class="w3-container">
            <p id="confirm-content"></p>
          </div>
          <footer class="w3-container w3-border-top w3-right-align w3-padding">
            <a class="w3-button w3-white w3-border" href="" id="confirm-url">
              <i class="fa-solid fa-chevron-right fa-padding-right"></i>
              Continue
            </a>
            <button class="w3-button w3-white w3-border" onclick="closeModal('confirm');">
              Cancel
            </button>
          </footer>
        </div>
      </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
  }

  let confirmContent = document.getElementById('confirm-content');
  let confirmUrl = document.getElementById('confirm-url');

  confirmContent.innerHTML = text;
  confirmUrl.href = url;

  openModal('confirm');
  return false;
}

function alertModal(text) {
  if (!document.getElementById('alert')) {
    const modalHtml = `
      <div id="alert" class="w3-modal" style="z-index: 200; opacity: 0; display: none">
        <div class="w3-modal-content w3-card-4">
          <div class="w3-container">
            <p id="alert-content"></p>
          </div>
          <footer class="w3-container w3-border-top w3-right-align w3-padding">
            <button class="w3-button w3-white w3-border" onclick="closeModal('alert');">
              OK
            </button>
          </footer>
        </div>
      </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
  }

  let alertContent = document.getElementById('alert-content');

  alertContent.innerHTML = text;

  openModal('alert');
  return false;
}