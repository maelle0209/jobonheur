let data = [];

// Function to load and parse the CSV
function loadCSV() {
  fetch('dataa.csv')  // Replace with the actual path to your CSV file
    .then(response => response.text())
    .then(csvText => {
      Papa.parse(csvText, {
        header: true,  // Use first line as header (keys for the objects)
        skipEmptyLines: true,  // Skip empty lines
        complete: function(results) {
          data = results.data;  // Parsed data as an array of objects
          console.log(data);  // Debugging - log parsed data
        }
      });
    })
    .catch(error => console.error("Error loading CSV file:", error));
}

// Call to load the CSV when the page is ready
loadCSV();

// Event listener for form submission
document.getElementById('filters-form').addEventListener('submit', function(event) {
  event.preventDefault();  // Prevent page refresh on form submission

  // Get the filter values from the form
  const age = document.getElementById('age').value;
  const jobRole = document.getElementById('job-role').value;
  const stressLevel = document.getElementById('stress-level').value;
  const physicalActivity = document.getElementById('physical-activity').value;
  const sleepQuality = document.getElementById('sleep-quality').value;

  // Apply the filters
  const filteredData = data.filter(item => {
    const matchesAge = age ? item['Age'] == age : true;
    const matchesJobRole = jobRole !== 'any' ? item['Job_Role'] === jobRole : true;
    const matchesStressLevel = stressLevel !== 'any' ? item['Stress_Level'] === stressLevel : true;
    const matchesPhysicalActivity = physicalActivity !== 'any' ? item['Physical_Activity'] === physicalActivity : true;
    const matchesSleepQuality = sleepQuality !== 'any' ? item['Sleep_Quality'] === sleepQuality : true;

    return matchesAge && matchesJobRole && matchesStressLevel && matchesPhysicalActivity && matchesSleepQuality;
  });

  // Display the filtered results
  displayResults(filteredData);
});

// Function to display filtered results
function displayResults(results) {
  const resultsList = document.getElementById('results-list');
  resultsList.innerHTML = '';  // Clear previous results

  if (results.length === 0) {
    resultsList.innerHTML = '<p>No results found.</p>';
  } else {
    results.forEach(item => {
      const resultItem = document.createElement('div');
      resultItem.classList.add('result-item');
      resultItem.innerHTML = `
        <p><strong>Rôle Professionnel:</strong> ${item['Job_Role']}</p>
        <p><strong>Âge:</strong> ${item['Age']}</p>
        <p><strong>Niveau de Stress:</strong> ${item['Stress_Level']}</p>
        <p><strong>Activité Physique:</strong> ${item['Physical_Activity']}</p>
        <p><strong>Qualité du Sommeil:</strong> ${item['Sleep_Quality']}</p>
      `;
      resultsList.appendChild(resultItem);
    });
  }
}
