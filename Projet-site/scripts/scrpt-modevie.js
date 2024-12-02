let data = []; // Données CSV chargées
let currentPage = 1; // Page actuelle
const resultsPerPage = 5; // Nombre de résultats par page

// Fonction pour charger et analyser le fichier CSV
function loadCSV() {
  fetch('dataa.csv') // Remplacez par le chemin réel du fichier CSV
    .then(response => response.text())
    .then(csvText => {
      Papa.parse(csvText, {
        header: true,
        skipEmptyLines: true,
        complete: function(results) {
          data = results.data; // Données analysées
          console.log(data); // Debug
        }
      });
    })
    .catch(error => console.error("Erreur de chargement du fichier CSV:", error));
}

// Appel pour charger le CSV lorsque la page est prête
loadCSV();

// Fonction pour calculer une correspondance basée sur les filtres
function calculateMatch(item, filters) {
  let score = 0;

  // Calcul de la correspondance pour chaque filtre
  if (filters.age && item['Age'] == filters.age) score += 20;
  if (filters.jobRole && item['Job_Role'] === filters.jobRole) score += 20;
  if (filters.stressLevel && item['Stress_Level'] === filters.stressLevel) score += 20;
  if (filters.physicalActivity && item['Physical_Activity'] === filters.physicalActivity) score += 20;
  if (filters.sleepQuality && item['Sleep_Quality'] === filters.sleepQuality) score += 20;

  return score; // Retourne un score de correspondance
}

// Fonction pour filtrer et trier les données par correspondance
function filterAndSortData(filters) {
  return data
    .map(item => ({
      ...item,
      matchScore: calculateMatch(item, filters) // Ajout du score de correspondance
    }))
    .filter(item => item.matchScore > 0) // Garde seulement les éléments avec une correspondance
    .sort((a, b) => b.matchScore - a.matchScore); // Trie par score décroissant
}

// Écouteur pour le formulaire
document.getElementById('filters-form').addEventListener('submit', function(event) {
  event.preventDefault();

  // Récupérer les valeurs des filtres
  const filters = {
    age: document.getElementById('age').value,
    jobRole: document.getElementById('job-role').value,
    stressLevel: document.getElementById('stress-level').value,
    physicalActivity: document.getElementById('physical-activity').value,
    sleepQuality: document.getElementById('sleep-quality').value
  };

  // Appliquer les filtres et trier les données
  const filteredData = filterAndSortData(filters);
  displayResults(filteredData, currentPage);
});

// Fonction pour afficher les résultats paginés
function displayResults(results, page) {
  const resultsList = document.getElementById('results-list');
  resultsList.innerHTML = ''; // Efface les résultats précédents

  // Calcul des indices pour la pagination
  const startIndex = (page - 1) * resultsPerPage;
  const endIndex = startIndex + resultsPerPage;
  const paginatedResults = results.slice(startIndex, endIndex);

  // Affichage des résultats paginés
  if (paginatedResults.length === 0) {
    resultsList.innerHTML = '<p>Aucun résultat trouvé.</p>';
  } else {
    paginatedResults.forEach(item => {
      const resultItem = document.createElement('div');
      resultItem.classList.add('result-item');
      resultItem.innerHTML = `
        <p><strong>Rôle Professionnel:</strong> ${item['Job_Role']}</p>
        <p><strong>Âge:</strong> ${item['Age']}</p>
        <p><strong>Industrie:</strong> ${item['Industry']}</p>
        <p><strong>Lieu de travail:</strong> ${item['Work_Location']}</p>
        <p><strong>Correspondance:</strong> ${item.matchScore}%</p>
      `;
      resultsList.appendChild(resultItem);
    });
  }

  // Gestion des boutons de pagination
  const paginationControls = document.getElementById('pagination-controls');
  paginationControls.innerHTML = ''; // Efface les contrôles précédents

  const totalPages = Math.ceil(results.length / resultsPerPage);

  if (totalPages > 1) {
    if (page > 1) {
      const prevButton = document.createElement('button');
      prevButton.textContent = 'Précédent';
      prevButton.addEventListener('click', () => {
        currentPage--;
        displayResults(results, currentPage);
      });
      paginationControls.appendChild(prevButton);
    }

    if (page < totalPages) {
      const nextButton = document.createElement('button');
      nextButton.textContent = 'Suivant';
      nextButton.addEventListener('click', () => {
        currentPage++;
        displayResults(results, currentPage);
      });
      paginationControls.appendChild(nextButton);
    }
  }
}
