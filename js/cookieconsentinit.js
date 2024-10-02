var isDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
window.cookieconsent.initialise({
    palette: {
        popup: { background: isDarkMode ? "#000000" : "#ffffff" },
        button: { background: "#0000ff" },
    },
    theme: "classic",
    location: true,
    showLink: false
});
