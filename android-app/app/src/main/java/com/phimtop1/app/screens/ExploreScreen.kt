package com.phimtop1.app.screens

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Search
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.navigation.NavController
import com.phimtop1.app.api.MovieItem
import com.phimtop1.app.api.RetrofitClient
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ExploreScreen(navController: NavController, initialKeyword: String? = null) {
    val coroutineScope = rememberCoroutineScope()
    var keyword by remember { mutableStateOf(initialKeyword ?: "") }
    var movies by remember { mutableStateOf<List<MovieItem>>(emptyList()) }
    var domain by remember { mutableStateOf("") }
    var isLoading by remember { mutableStateOf(false) }
    var errorMessage by remember { mutableStateOf<String?>(null) }
    
    // Add trending movies state
    var trendingMovies by remember { mutableStateOf<List<MovieItem>>(emptyList()) }
    var isTrendingLoading by remember { mutableStateOf(true) }

    val apiKey = RetrofitClient.API_KEY
    
    // Fetch trending movies on init
    LaunchedEffect(Unit) {
        coroutineScope.launch {
            try {
                val response = RetrofitClient.instance.getHome(apiKey)
                if (response.status == "success") {
                    trendingMovies = response.data.items.take(12)
                    domain = response.data.domain
                }
            } catch (e: Exception) {
                e.printStackTrace()
            } finally {
                isTrendingLoading = false
            }
        }
    }

    // Debounce search
    LaunchedEffect(keyword) {
        if (keyword.trim().isEmpty()) {
            movies = emptyList()
            return@LaunchedEffect
        }
        
        isLoading = true
        errorMessage = null
        
        // Wait for user to stop typing
        delay(500)
        
        coroutineScope.launch {
            try {
                val response = RetrofitClient.instance.searchMovies(apiKey, keyword.trim())
                if (response.status == "success") {
                    movies = response.data.items
                    domain = response.data.domain
                    if (movies.isEmpty()) {
                        errorMessage = "Không tìm thấy phim nào."
                    }
                } else {
                    errorMessage = "Lỗi khi tìm kiếm."
                }
            } catch (e: Exception) {
                e.printStackTrace()
                errorMessage = "Không thể kết nối đến máy chủ."
            } finally {
                isLoading = false
            }
        }
    }

    Column(modifier = Modifier.fillMaxSize()) {
        // Search Bar
        OutlinedTextField(
            value = keyword,
            onValueChange = { keyword = it },
            placeholder = { Text("Tìm kiếm tên phim...") },
            leadingIcon = { Icon(Icons.Filled.Search, contentDescription = "Search") },
            shape = RoundedCornerShape(24.dp),
            modifier = Modifier
                .fillMaxWidth()
                .padding(16.dp),
            singleLine = true,
            colors = TextFieldDefaults.outlinedTextFieldColors(
                focusedBorderColor = MaterialTheme.colorScheme.primary,
                unfocusedBorderColor = MaterialTheme.colorScheme.outline
            )
        )

        // Results Area
        Box(modifier = Modifier.fillMaxSize().weight(1f)) {
            if (isLoading) {
                CircularProgressIndicator(modifier = Modifier.align(Alignment.Center))
            } else if (errorMessage != null) {
                Text(
                    text = errorMessage!!,
                    color = MaterialTheme.colorScheme.onSurfaceVariant,
                    modifier = Modifier.align(Alignment.Center)
                )
            } else if (movies.isNotEmpty()) {
                LazyVerticalGrid(
                    columns = GridCells.Fixed(2),
                    contentPadding = PaddingValues(bottom = 16.dp),
                    modifier = Modifier.fillMaxSize()
                ) {
                    items(movies) { movie ->
                        YoukuMovieCard(
                            movie = movie,
                            domain = domain,
                            onClick = { navController.navigate("movie_detail/${movie.slug}") }
                        )
                    }
                }
            } else if (keyword.isEmpty()) {
                if (isTrendingLoading) {
                    CircularProgressIndicator(modifier = Modifier.align(Alignment.Center))
                } else if (trendingMovies.isNotEmpty()) {
                    Column(
                        modifier = Modifier.fillMaxSize().padding(horizontal = 16.dp)
                    ) {
                        Text(
                            text = "Phim Thịnh Hành",
                            style = MaterialTheme.typography.titleMedium,
                            color = MaterialTheme.colorScheme.primary,
                            modifier = Modifier.padding(bottom = 12.dp)
                        )
                        LazyVerticalGrid(
                            columns = GridCells.Fixed(2),
                            contentPadding = PaddingValues(bottom = 16.dp),
                            modifier = Modifier.fillMaxSize(),
                            horizontalArrangement = Arrangement.spacedBy(12.dp),
                            verticalArrangement = Arrangement.spacedBy(16.dp)
                        ) {
                            items(trendingMovies) { movie ->
                                YoukuMovieCard(
                                    movie = movie,
                                    domain = domain,
                                    modifier = Modifier.fillMaxWidth(),
                                    onClick = { navController.navigate("movie_detail/${movie.slug}") }
                                )
                            }
                        }
                    }
                } else {
                    Text(
                        text = "Hãy nhập tên phim bạn muốn tìm.",
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                        modifier = Modifier.align(Alignment.Center)
                    )
                }
            }
        }
    }
}
