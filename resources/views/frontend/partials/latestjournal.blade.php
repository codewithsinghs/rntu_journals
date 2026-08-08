
<section class="latest_journal_section">
    <div class="s__container_custom">
        <div class="journal_top text-center">
            <span class="journal_tag d-none" id="latestJournalTitle"></span>
            <h2 id="latestJournalHeading"></h2>
            <p id="latestJournalDescription"></p>
        </div>

        <div class="row gy-5">
            <div class="col-lg-6">
                <div class="journal_wrapper">
                    <div class="journal_heading">
                        <h3>Journals</h3>
                    </div>
                    <div class="js-journal-wrapper-simple"></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="issues_wrapper">
                    <div class="journal_heading">
                        <h3>Issues</h3>
                    </div>
                    <div class="issue_tabs" id="issueTabs">
                        <button class="tab-btn active" onclick="openCity(event,'tab-latest')">Latest</button>
                    </div>
                    <div id="issueTabsContent">
                        <div id="tab-latest" class="w3-container city" style="display:block"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Home Tabs Js
    function openCity(event, cityName) {
        let city = document.getElementsByClassName("city");

        for (let i = 0; i < city.length; i++) {
            city[i].style.display = "none";
        }

        let buttons = document.getElementsByClassName("tab-btn");

        for (let i = 0; i < buttons.length; i++) {
            buttons[i].classList.remove("active");
        }

        document.getElementById(cityName).style.display = "block";

        event.currentTarget.classList.add("active");
    }
</script>
<<<<<<< HEAD
=======


>>>>>>> main
