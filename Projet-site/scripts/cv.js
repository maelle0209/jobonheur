const form = document.getElementById('cv-form');
const previewBtn = document.getElementById('preview-btn');
const downloadBtn = document.getElementById('download-btn');

// Prévisualisation
previewBtn.addEventListener('click', () => {
    const name = document.getElementById('name').value || "Votre nom ici";
    const title = document.getElementById('title').value || "Titre professionnel";
    const experience = document.getElementById('experience').value || "Expérience...";
    const skills = document.getElementById('skills').value || "Compétences...";
    const contact = document.getElementById('contact').value || "Contact ici";
    const education = document.getElementById('education').value || "Éducation...";
    const languages = document.getElementById('languages').value || "Langues...";
    const photo = document.getElementById('photo').files[0];

    // Mettre à jour l'aperçu
    document.getElementById('preview-name').textContent = name;
    document.getElementById('preview-title').textContent = title;
    document.getElementById('preview-experience').textContent = experience;
    document.getElementById('preview-skills').textContent = skills;
    document.getElementById('preview-contact').textContent = contact;
    document.getElementById('preview-education').textContent = education;
    document.getElementById('preview-languages').textContent = languages;

    if (photo) {
        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById('preview-photo').src = e.target.result;
        };
        reader.readAsDataURL(photo);
    }
});

// Téléchargement en PDF
downloadBtn.addEventListener('click', () => {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF();

    // Coordonnées initiales
    let y = 20;

    // Ajout du titre
    pdf.setFontSize(16);
    pdf.text("CV", 105, y, { align: "center" });

    y += 10;

    // Ajout de la photo (si disponible)
    const photo = document.getElementById('photo').files[0];
    if (photo) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const imgData = e.target.result;
            pdf.addImage(imgData, 'JPEG', 10, y, 40, 40); // Position x, y, largeur, hauteur
            generatePDFContent(y + 50); // Décale le contenu
        };
        reader.readAsDataURL(photo);
    } else {
        generatePDFContent(y);
    }

    function generatePDFContent(startY) {
        y = startY;

        pdf.setFontSize(12);
        pdf.text(`Nom: ${document.getElementById('preview-name').textContent}`, 60, y);
        y += 10;

        pdf.text(`Titre: ${document.getElementById('preview-title').textContent}`, 60, y);
        y += 10;

        pdf.text(`Contact: ${document.getElementById('preview-contact').textContent}`, 60, y);
        y += 20;

        pdf.setFontSize(14);
        pdf.text("Expérience:", 10, y);
        pdf.setFontSize(12);
        pdf.text(document.getElementById('preview-experience').textContent, 20, y + 10);
        y += 30;

        pdf.setFontSize(14);
        pdf.text("Compétences:", 10, y);
        pdf.setFontSize(12);
        pdf.text(document.getElementById('preview-skills').textContent, 20, y + 10);
        y += 30;

        pdf.setFontSize(14);
        pdf.text("Éducation:", 10, y);
        pdf.setFontSize(12);
        pdf.text(document.getElementById('preview-education').textContent, 20, y + 10);
        y += 30;

        pdf.setFontSize(14);
        pdf.text("Langues:", 10, y);
        pdf.setFontSize(12);
        pdf.text(document.getElementById('preview-languages').textContent, 20, y + 10);
        y += 30;

        // Sauvegarde le fichier PDF
        pdf.save("CV.pdf");
    }
});
