
// Route Thể loại
app.get('/the-loai/:slug', async (req, res) => {
    try {
        const page = parseInt(req.query.page) || 1;
        const limit = 36;
        const offset = (page - 1) * limit;

        const genre = GENRES.find(g => g.slug === req.params.slug);
        const typeName = genre ? genre.name : req.params.slug;

        const [countRes] = await pool.query('SELECT COUNT(id) as total FROM movies WHERE categories_json LIKE ?', [`%${typeName}%`]);
        const [movies] = await pool.query('SELECT name, slug, year, origin_name, status, episode_current, type, thumb_url, updated_at FROM movies WHERE categories_json LIKE ? ORDER BY updated_at DESC LIMIT ? OFFSET ?', [`%${typeName}%`, limit, offset]);

        res.render('index', { 
            totalMovies: countRes[0].total,
            todayMovies: 0,
            movies: movies,
            page: page,
            isSearch: false,
            isCategory: true,
            categoryType: 'genre',
            categoryName: typeName,
            baseUrl: '/the-loai/' + req.params.slug,
            keyword: '',
            globalGenres: GENRES,
            globalCountries: COUNTRIES
        });
    } catch (err) {
        res.status(500).send(err.message);
    }
});

// Route Quốc gia
app.get('/quoc-gia/:slug', async (req, res) => {
    try {
        const page = parseInt(req.query.page) || 1;
        const limit = 36;
        const offset = (page - 1) * limit;

        const country = COUNTRIES.find(c => c.slug === req.params.slug);
        const typeName = country ? country.name : req.params.slug;

        const [countRes] = await pool.query('SELECT COUNT(id) as total FROM movies WHERE countries_json LIKE ?', [`%${typeName}%`]);
        const [movies] = await pool.query('SELECT name, slug, year, origin_name, status, episode_current, type, thumb_url, updated_at FROM movies WHERE countries_json LIKE ? ORDER BY updated_at DESC LIMIT ? OFFSET ?', [`%${typeName}%`, limit, offset]);

        res.render('index', { 
            totalMovies: countRes[0].total,
            todayMovies: 0,
            movies: movies,
            page: page,
            isSearch: false,
            isCategory: true,
            categoryType: 'country',
            categoryName: typeName,
            baseUrl: '/quoc-gia/' + req.params.slug,
            keyword: '',
            globalGenres: GENRES,
            globalCountries: COUNTRIES
        });
    } catch (err) {
        res.status(500).send(err.message);
    }
});
