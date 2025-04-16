function startLoader() {
  document.querySelector(".loaderContainer").style.display = "grid";
}
function stopLoader() {
  document.querySelector(".loaderContainer").style.display = "none";
}

function togglePassword(event){
  let span = event.target;
  let input = span.previousElementSibling;
  if (input.type === "password") {
    input.type = "text";
    span.innerHTML = 'visibility_off';
  } else {
    input.type = "password";
    span.innerHTML = 'visibility';
  }
}