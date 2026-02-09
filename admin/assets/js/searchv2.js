const searchBox = document.getElementById("searchBox");
const searchForm = document.getElementById("searchForm");
const suggestionsBox = document.getElementById("suggestions");
const detailsBox = document.getElementById("employeeDetails");


let currentSuggestions = []; // latest suggestions
let activeIndex = -1; // track highlighted suggestion

// Render suggestions
function renderSuggestions(data) {

    suggestionsBox.innerHTML = "";
    currentSuggestions = data;
    activeIndex = -1;

    if (data.length > 0) {
        data.forEach((item, index) => {
            let div = document.createElement("div");
            div.textContent = `${item.empid} - ${item.name} (${item.position})`;

            div.onclick = () => {
                selectSuggestion(index);
            };

            suggestionsBox.appendChild(div);
        });
    } else {
        suggestionsBox.innerHTML = "<div onClick='changeSearchSource()'>No results found click to deep search</div>";
    }
}

// Select a suggestion (click or Enter)
function selectSuggestion(index) {
    if (index >= 0 && index < currentSuggestions.length) {
        const selected = currentSuggestions[index];
        suggestionsBox.innerHTML = "";

        let form = document.getElementById("myForm");

        let searchSourceID = document.createElement("input");
        searchSourceID.type = "hidden";
        searchSourceID.name = "searchSourceID";
        searchForm.appendChild(searchSourceID);

        searchBox.value = selected.empid;
        searchSourceID.value = selected.searchSourceID;
        
        searchForm.submit();
    }
}

function changeSearchSource(){
    performSearch(searchBox, 1)
}

function performSearch(searchBox, searchSource=0){
    
    let query = searchBox.value.trim();

    if (query.length > 0) {
        
        let formData = new FormData();
        formData.append('q', encodeURIComponent(query));
        formData.append('searchSource', searchSource);
        formData.append('csrf_token', csrfToken);

        suggestionsBox.style.display="block";
        fetch("payslip.php", {
          method: "POST",
          body: formData
        })
        .then(res => res.json())
        .then(data => renderSuggestions(data));
    } else {
        suggestionsBox.style.display="none";
        suggestionsBox.innerHTML = "";
        currentSuggestions = [];
        activeIndex = -1;
    }
}

searchBox.addEventListener("keydown", function (e) {
    const items = suggestionsBox.querySelectorAll("div");

    if (e.key === "ArrowDown") {
        e.preventDefault();
        if (currentSuggestions.length > 0) {
            activeIndex = (activeIndex + 1) % currentSuggestions.length;
            items.forEach((el, i) => el.classList.toggle("active", i === activeIndex));
        }
    }

    if (e.key === "ArrowUp") {
        e.preventDefault();
        if (currentSuggestions.length > 0) {
            activeIndex = (activeIndex - 1 + currentSuggestions.length) % currentSuggestions.length;
            items.forEach((el, i) => el.classList.toggle("active", i === activeIndex));
        }
    }

    if (e.key === "Enter") {
        e.preventDefault();
        if (activeIndex >= 0) {
            selectSuggestion(activeIndex);
        } else if (currentSuggestions.length > 0) {
            // Default: first suggestion
            selectSuggestion(0);
        }
    }
});

searchBox.addEventListener("keyup", function (e) {
    if (["ArrowDown", "ArrowUp", "Enter"].includes(e.key)) return;

    performSearch(this);
});

document.addEventListener("DOMContentLoaded", function() {
    const suggestionsBox = document.getElementById("suggestions");
    const searchresult = document.getElementById("search-message-result");

    // Show hint in suggestion box
    suggestionsBox.innerHTML = `
        <div style="padding:8px; color:#555; font-size:14px;">
            🔎 You can search by <b>Employee ID</b>, <b>Name</b>, or <b>Position</b>
        </div>`;
    suggestionsBox.style.display = "block";
    searchresult.style.display = "block";

    // Hide after 10 seconds
    setTimeout(() => {
        suggestionsBox.style.display = "none";
        searchresult.style.display = "none";
        suggestionsBox.innerHTML = "";
        searchresult.innerHTML = "";
    }, 10000);
});
