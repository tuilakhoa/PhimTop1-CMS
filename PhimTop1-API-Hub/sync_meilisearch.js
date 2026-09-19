require('dotenv').config();
const mysql = require('mysql2/promise');
const { MeiliSearch } = require('meilisearch');

const client = new MeiliSearch({
  host: process.env.MEILI_HOST || 'http://127.0.0.1:7700',
  apiKey: process.env.MEILI_MASTER_KEY || 'masterKey123'
});

async function syncMovies() {
    const pool = mysql.createPool({
        host: process.env.DB_HOST || 'localhost',
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || '',
        database: process.env.DB_NAME || 'phimtop1_cms',
    });

    try {
        console.log('Fetching movies from MySQL...');
        const [movies] = await pool.query('SELECT slug as id, name, origin_name, year, type, status, thumb_url, actor, director, content, tmdb_vote FROM movies');
        
        console.log(`Found ${movies.length} movies. Pushing to Meilisearch...`);
        
        const index = client.index('movies');
        
        await index.addDocuments(movies);
        
        console.log('Configuring search settings...');
        await index.updateSearchableAttributes([
            'name',
            'origin_name',
            'actor',
            'director'
        ]);
        
        await index.updateFilterableAttributes(['year', 'type']);
        
        console.log('✅ Sync completed successfully!');
    } catch (e) {
        console.error('❌ Sync failed:', e);
    } finally {
        process.exit(0);
    }
}

syncMovies();
