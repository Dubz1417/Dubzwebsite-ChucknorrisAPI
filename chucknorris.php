<?php 
require_once 'auth_check.php';
$page_title = "Chuck Norris Jokes - Dubz Adventours";
include 'header.php'; 
?>

<main>
    <section class="page-header">
        <div class="container"> 
            <p>Need a laugh between adventures? Check out these legendary Chuck Norris Jokes</p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="joke-main-card">
                <div class="joke-icon">😄</div>
                <h2 class="joke-title">Chuck Norris jokes</h2>
                <div class="joke-display" id="joke-content">
                    <p class="loading">Loading</p>
                </div>
                <button class="btn btn-primary btn-joke" onclick="fetchJoke()">
                    Get Another Joke
                </button>
            </div>

            <div class="joke-info">
                <p> </p>
                <p class="api-credit"> <a href="https://api.chucknorris.io" target="_blank">ChuckNorris.io API</a></p>
            </div>
        </div>
    </section>
</main>

<style>
.joke-main-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    padding: 3rem;
    max-width: 800px;
    margin: 0 auto 3rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    border: 2px solid rgba(255, 107, 53, 0.2);
    text-align: center;
}

.joke-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.joke-title {
    color: var(--primary-orange);
    font-size: 2rem;
    margin-bottom: 2rem;
    font-weight: 700;
}

.joke-display {
    background: linear-gradient(135deg, rgba(255, 107, 53, 0.05), rgba(255, 183, 77, 0.05));
    border-left: 4px solid var(--primary-orange);
    padding: 2rem;
    border-radius: 10px;
    min-height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 2rem;
}

.joke-text {
    font-size: 1.3rem;
    line-height: 1.8;
    color: #333;
    font-weight: 500;
    font-style: italic;
}

.loading {
    color: #999;
    font-size: 1.1rem;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.error {
    color: #dc3545;
    font-size: 1.1rem;
}

.btn-joke {
    font-size: 1.1rem;
    padding: 1rem 2.5rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.joke-info {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    max-width: 800px;
    margin: 0 auto;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.joke-info h3 {
    color: var(--primary-orange);
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.joke-info p {
    color: #666;
    line-height: 1.8;
    margin-bottom: 1rem;
}

.api-credit {
    font-size: 0.9rem;
    color: #999;
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid #eee;
}

.api-credit a {
    color: var(--primary-orange);
    text-decoration: none;
    font-weight: 600;
}

.api-credit a:hover {
    text-decoration: underline;
}
</style>

<script>
async function fetchJoke() {
    const jokeContent = document.getElementById('joke-content');
    jokeContent.innerHTML = '<p class="loading">Loading an epic Chuck Norris fact...</p>';
    
    try {
        const response = await fetch('https://api.chucknorris.io/jokes/random');
        const data = await response.json();
        jokeContent.innerHTML = `<p class="joke-text">"${data.value}"</p>`;
    } catch (error) {
        jokeContent.innerHTML = '<p class="error">Oops! Could not fetch a joke. Even Chuck Norris has off days!</p>';
    }
}


fetchJoke();
</script>

<?php include 'footer.php'; ?>
