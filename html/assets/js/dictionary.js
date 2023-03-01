
function dictionary_fetch(event) {
	//get entry ID
	let a = null;
	if (event.target.tagName == "a") {
		a = event.target;
	} else {
		a = event.target.closest("a.dictionary-search-result");
	}
	if (typeof a.dataset.entry === "undefined" || a.dataset.entry.length < 1) {
		return;
	}
	if (a.classList.contains("dictionary-search-active")) {
		return;
	}
	//update sidebar DOM
	let list = document.getElementsByClassName("dictionary-search-active");
	while (list.length > 0) {
		list[0].classList.remove("dictionary-search-active");
	}
	a.classList.add("dictionary-search-active");
	//update address bar
	let url = new URL(window.location.href);
	url.searchParams.set("id",a.dataset.entry);
	url.searchParams.delete("lang");
	url.hash = a.dataset.morph;
	history.replaceState(null,"", url.toString());
	//perform request and update entry display DOM
	let search_url = "/lore/util/fetch.php?id="+a.dataset.entry;
	let xhr = new XMLHttpRequest();
	xhr.open('GET', search_url, true);
	xhr.responseType = 'text';
	xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
	xhr.onload = function() {
		let status = xhr.status;
		if (status === 200) {
			let elem = document.getElementById("dictionary-display");
			elem.innerHTML = xhr.response;
			dictionary_highlight();
		} else {
			console.log("HTTP "+status+" from "+search_url);
		}
	};
	xhr.send();
}

let e = document.querySelector("#dictionary-search>form");
e.addEventListener("submit",function(e) {
	//get form data
	e.preventDefault();
	const data = new FormData(e.target);
	//update address bar
	let url = new URL(window.location.href);
	url.searchParams.set("type",data.get("type"));
	url.searchParams.set("key",data.get("key"));
	history.replaceState(null,"", url.toString());
	//perform request and update sidebar DOM
	let search_url = "/lore/util/search.php?type="+encodeURIComponent(data.get("type"))+"&key="+encodeURIComponent(data.get("key"));
	if (url.searchParams.has("id")) {
		search_url += "&id="+url.searchParams.get("id");
	}
	if (url.hash.length > 0) {
		search_url += "&fr="+url.hash.slice(1);
	}
	let xhr = new XMLHttpRequest();
	xhr.open('GET', search_url, true);
	xhr.responseType = 'text';
	xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
	xhr.onload = function() {
		let status = xhr.status;
		if (status === 200) {
			let elem = document.getElementById("dictionary-search-results");
			elem.innerHTML = xhr.response;
		} else {
			console.log("HTTP "+status+" from "+search_url);
		}
	};
	xhr.send();
});

//in some cases, on first pageload search.php returns a search result list with multiple items highlighted because PHP can't access the URL hash ... so this is a Javascript band-aid
if (window.location.hash.length > 0) {
	let list = document.getElementsByClassName("dictionary-search-active");
	while (list.length > 1) {
		for (let i=0; i < list.length; i++) {
			if (list[i].dataset.morph != window.location.hash.slice(1)) {
				list[i].classList.remove("dictionary-search-active");
			}
		}
	}
}

function dictionary_highlight() {
	let margin = 3;
	if (window.location.hash.length < 1) {
		return;
	}
	let id = window.location.hash.slice(1);
	let target = document.getElementById(id);
	if (typeof target === "undefined" || target === null) {
		console.log("oopsies");
		return;
	}
	let container = target.closest("h3,tr");
	let parent = document.getElementById("dictionary-display");
	let e = document.createElement("span");
	e.classList.add("dictionary-highlight");
	e.style.display = "block";
	e.style.position = "absolute";
	let rect = container.getBoundingClientRect();
	e.style.width = (rect.width+margin*2).toString()+"px";
	e.style.height = (rect.height+margin*2).toString()+"px";
	e.style.zIndex = "10";
	e.style.top = (rect.top+window.scrollY-margin).toString()+"px";
	e.style.left = (rect.left-margin).toString()+"px";
	e.style.pointerEvents = "none";
	parent.appendChild(e);
}

window.onload = dictionary_highlight;

