const dropBox = document.getElementById("dropBox");
const uploadImg = document.getElementById("upload-img");

dropBox.addEventListener("click", () => {
  uploadImg.click();
});

export function clearImage() {
  dropBox.style.backgroundImage = "";
  dropBox.innerHTML = `<i class="fa-solid fa-cloud-arrow-up fa-2x mb-2"></i>
                          <span>Click or drag file to upload</span>`;
  uploadImg.value = "";
}

function handleFile(file) {
  if (!file.type.startsWith("image/")) {
    Toast.error("Please select a valid image file.");
    return;
  }

  const imgLink = URL.createObjectURL(file);
  dropBox.style.backgroundImage = `url(${imgLink})`;
  dropBox.innerHTML = "";
}

// uploadImg.addEventListener("cancel", function () {
//   clearImage();
// });
uploadImg.addEventListener("change", function () {
  if (uploadImg.files.length > 0) {
    handleFile(uploadImg.files[0]);
  } else {
    clearImage();
  }
});

dropBox.addEventListener("dragover", function (e) {
  e.preventDefault();
  dropBox.classList.add("dragging");
});

dropBox.addEventListener("dragleave", function () {
  dropBox.classList.remove("dragging");
});

dropBox.addEventListener("drop", function (e) {
  e.preventDefault();
  dropBox.classList.remove("dragging");

  const files = e.dataTransfer.files;
  if (files.length > 0) {
    handleFile(files[0]);
  }
});
