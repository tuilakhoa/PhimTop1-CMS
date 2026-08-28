<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/repo/MovieRepository.php';
require_once __DIR__ . '/repo/CategoryRepository.php';
require_once __DIR__ . '/repo/CommentRepository.php';
require_once __DIR__ . '/repo/SeoRepository.php';

function getFirestoreInstance() {
    static $instance = null;
    if ($instance !== null) return $instance;
    
    $config = getDbConfig();
    if ($config && isset($config['type']) && $config['type'] === 'firestore') {
        require_once __DIR__ . '/firestore_helper.php';
        $instance = new FirestoreClient($config['projectId'], $config['serviceAccount']);
        return $instance;
    }
    return null;
}

function getMovieRepository() {
    static $instance = null;
    if ($instance === null) {
        $instance = new MovieRepository(getPDO(), getFirestoreInstance());
    }
    return $instance;
}

function getCategoryRepository() {
    static $instance = null;
    if ($instance === null) {
        $instance = new CategoryRepository(getPDO(), getFirestoreInstance());
    }
    return $instance;
}

function getCommentRepository() {
    static $instance = null;
    if ($instance === null) {
        $instance = new CommentRepository(getPDO(), getFirestoreInstance());
    }
    return $instance;
}

function getSeoRepository() {
    static $instance = null;
    if ($instance === null) {
        $instance = new SeoRepository(getPDO(), getFirestoreInstance());
    }
    return $instance;
}
