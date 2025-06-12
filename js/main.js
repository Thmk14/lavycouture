document.getElementById('mode').addEventListener('change', function () {
    const paiementSection = document.getElementById('section-paiement');
    if (this.value === "Paiement par mobile money") {
        paiementSection.style.display = "block";
    } else {
        paiementSection.style.display = "none";
    }
});

document.getElementById('btn-payer').addEventListener('click', function () {
    const montant = document.getElementById('montant').value;

    if (!montant || isNaN(montant)) {
        alert("Veuillez entrer un montant valide.");
        return;
    }

    openKkiapayWidget({
        amount: montant,
        position: "center",
        callback: "/success",
        data: "Lavy couture",
        theme: "pink",
        sandbox: true,
        currency: "XOF",
        key: "c60da420328411f0abd677a28fcabbff" // Remplace par ta vraie clé
    });
});

