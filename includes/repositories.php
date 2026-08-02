<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/repo/MovieRepository.php';
require_once __DIR__ . '/repo/CategoryRepository.php';
require_once __DIR__ . '/repo/CommentRepository.php';
require_once __DIR__ . '/repo/SeoRepository.php';

function getFirestoreInstance() {
    $config = getDbConfig();
    if ($config && isset($config['type']) && $config['type'] === 'firestore') {
        require_once __DIR__ . '/firestore_helper.php';
        return new FirestoreClient($config['projectId'], $config['serviceAccount']);
    }
    return null;
}

function getMovieRepository() {
    return new MovieRepository(getPDO(), getFirestoreInstance());
}

function getCategoryRepository() {
    return new CategoryRepository(getPDO(), getFirestoreInstance());
}

function getCommentRepository() {
    return new CommentRepository(getPDO(), getFirestoreInstance());
}

function getSeoRepository() {
    return new SeoRepository(getPDO(), getFirestoreInstance());
}
