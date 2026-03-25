window.Echo.channel("chat")
.listen("MessageSent", (e) => {
    console.log("Nouveau message:", e.message);

    document.querySelector("#chat").innerHTML += `
        <div>${e.message}</div>
    `;
});