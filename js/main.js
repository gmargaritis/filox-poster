window.onload = function(){

  let submitBtn = document.getElementById("submit");
  let postTitle = document.getElementById("title");
  let postContent = document.getElementById("content");
  let form = document.getElementById("form");
  let titleExists;
  let titleState = false;
  let typingTimer;
  let doneTypingInterval = 1000;
 

  form.addEventListener('submit', createPost, false);
  form.submit = createPost;

  postTitle.addEventListener('keyup', () => {
    clearTimeout(typingTimer);
    if (postTitle.value) {
      typingTimer = setTimeout(validateTitle, doneTypingInterval);
      if (postContent.value && (titleState == true)) {
        submitBtn.disabled = false;
      }
    }
  });

  postContent.addEventListener('keyup', () => {
    clearTimeout(typingTimer);
    if (postTitle.value && postContent.value && (titleState == true)) {
      typingTimer = setTimeout(() => submitBtn.disabled = false ,doneTypingInterval);
    }
  });

  async function validateTitle() {
    let response = await fetch(`${base_url}/wp-json/filox-poster/v1/validation`, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({title: postTitle.value})
    });
    titleExists = await response.json();

    if (titleExists == true) {
      alert('Title already exists! Enter a new one.');
      titleState = false;
    } else {
      titleState = true;
    }

    if (postContent.value && (titleState == true)) {
      submitBtn.disabled = false;
    } else {
      submitBtn.disabled = true;
    }
  }

  async function createPost(e) {
    if (e)  {e.preventDefault();}
    let response = await fetch(`${base_url}/wp-json/filox-poster/v1/post-creation`, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ title: postTitle.value, content: postContent.value}),
    });
    if (response.ok) {
      form.style.display = "none";
      let data = await response.json();
      postURL = data.url;
      
      let a = document.createElement("a");
      let node = document.createTextNode("Check out your new post!");
      a.appendChild(node);
      a.title = "Check out your new post!";
      a.href = postURL;

      let element = document.getElementById("url");
      element.appendChild(a);
      return response;
    } else {
      alert('error')
    }
  }
}
