<?php


class oFim_Permalink
{


    public function __construct()
    {


    }
    public function register(){
        add_action('admin_init', array( $this, 'settingsInit'));
        add_action('admin_init', array( $this, 'settingsSave'));
    }


    public function settingsInit() {
        $this->addField('', array($this, 'slug_title'), '');
        $this->addField('movie_slug_movie', array( $this, 'movie_movie'), 'Movie' );
        $this->addField('movie_slug_directors', array( $this, 'movie_directors'), 'Directors' );
        $this->addField('movie_slug_categories', array( $this, 'movie_categories'), 'Categories' );
        $this->addField('movie_slug_actors', array( $this, 'movie_actors'), 'Actors' );
        $this->addField('movie_slug_genres', array( $this, 'movie_genres'), 'Genres' );
        $this->addField('movie_slug_regions', array( $this, 'movie_regions'), 'Regions' );
        $this->addField('movie_slug_tags', array( $this, 'movie_tags'), 'Tags' );
        $this->addField('movie_slug_years', array( $this, 'movie_years'), 'Years' );
        $this->addField('movie_slug_details', array( $this, 'movie_watch_urls'), 'Play Movie Page' );
    }

    /* Callbacks
	-------------------------------------------------------------------------------
	*/
    public function slug_title() {
        echo '<h3 id="dooplay-permalinks">PHIMTOP1 Permalink Settings</h3>';
    }

    public function movie_directors() {
        echo $this->input('movie_slug_directors', 'directors', '/benal-tairi/');
    }
    public function movie_movie() {
        echo $this->input('movie_slug_movies', 'movie', '/nang-tien-ca/');
    }
    public function movie_categories() {
        echo $this->input('movie_slug_categories', 'categories', '/phim-cu/');
    }
    public function movie_actors() {
        echo $this->input('movie_slug_actors', 'actors', '/khanh-phuong/');
    }
    public function movie_genres() {
        echo $this->input('movie_slug_genres', 'genres', '/kinh-di/');
    }
    public function movie_regions() {
        echo $this->input('movie_slug_regions', 'regions', '/quoc-gia/');
    }
    public function movie_tags() {
        echo $this->input('movie_slug_tags', 'tags', '/lien-quan/');
    }
    public function movie_years() {
        echo $this->input('movie_slug_years', 'years', '/nam/');
    }
    public function movie_watch_urls() {
        echo $this->input('movie_watch_urls', 'xem-phim', '/quan-tro-nhu-y/');
    }


    public function settingsSave() {
        if ( ! is_admin() ) return;
        $this->saveField('movie_slug_directors');
        $this->saveField('movie_slug_movies');
        $this->saveField('movie_slug_categories');
        $this->saveField('movie_slug_actors');
        $this->saveField('movie_slug_genres');
        $this->saveField('movie_slug_tags');
        $this->saveField('movie_slug_years');
        $this->saveField('movie_slug_regions');
        $this->saveField('movie_watch_urls');
    }


    public function input( $option_name, $placeholder, $type ) {
        $slug = get_option( $option_name );
        $value = ( isset( $slug ) ) ? esc_attr( $slug ) : '';
        $utype = ($type) ? '<code>'. $type .'</code>' : null;

        return '<code>'. home_url() .'/</code><input class="dt_permaliks_input" name="'. $option_name .'" type="text" class="regular-text code" value="'. $slug .'" placeholder="'. $placeholder .'" />'. $utype;
    }

    public function addField( $option_name, $callback, $title ){
        add_settings_field(
            $option_name, // id
            $title,       // setting title
            $callback,    // display callback
            'permalink',  // settings page
            'optional'    // settings section
        );
    }
    public function saveField( $option_name ){
        if ( isset( $_POST[$option_name] ) ) {
            $permalink_structure = sanitize_title( $_POST[$option_name] );
            $permalink_structure = untrailingslashit( $permalink_structure );

            update_option( $option_name, $permalink_structure );
        }
    }


}