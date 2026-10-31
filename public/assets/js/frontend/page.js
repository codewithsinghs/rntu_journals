document.addEventListener("DOMContentLoaded", async function () {
    const slug = document.getElementById("pageScript").dataset.slug;

    try {
        const res = await fetch(`/api/public/pages/${slug}`);
        const json = await res.json();

        document.getElementById("pageLoading").style.display = "none";

        if (!json.status) {
            document.getElementById("pageError").style.display = "block";
            return;
        }

        const page = json.data;

        document.title = page.meta_title || page.title;
        document.getElementById("pgTitle").textContent = page.title;

        // Fix content that was saved as a code block or as escaped text
        let html = page.content || "";
        if (/^\s*<pre[\s>]/i.test(html) || html.includes("&lt;div")) {
            const tmp = document.createElement("div");
            tmp.innerHTML = html;
            html = tmp.textContent;
        }

        document.getElementById("pgBody").innerHTML = html;

        if (page.meta_description) {
            let tag = document.querySelector('meta[name="description"]');
            if (!tag) {
                tag = document.createElement("meta");
                tag.name = "description";
                document.head.appendChild(tag);
            }
            tag.content = page.meta_description;
        }

        document.getElementById("pageContent").style.display = "block";
    } catch (e) {
        document.getElementById("pageLoading").style.display = "none";
        document.getElementById("pageError").style.display = "block";
    }
});