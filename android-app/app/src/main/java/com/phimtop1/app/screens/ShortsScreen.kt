package com.phimtop1.app.screens

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.navigation.NavController
import com.phimtop1.app.api.MovieItem
import com.phimtop1.app.api.RetrofitClient
import kotlinx.coroutines.launch

@Composable
fun ShortsScreen(navController: NavController) {
    val coroutineScope = rememberCoroutineScope()
    var movies by remember { mutableStateOf<List<MovieItem>>(emptyList()) }
    var domain by remember { mutableStateOf("") }
    var isLoading by remember { mutableStateOf(true) }

    val apiKey = RetrofitClient.API_KEY

    LaunchedEffect(Unit) {
        coroutineScope.launch {
            try {
                val response = RetrofitClient.instance.getCategory(apiKey, "the-loai", "phim-ngan")
                if (response.status == "success") {
                    movies = response.data.items
                    domain = response.data.domain
                }
            } catch (e: Exception) {
                e.printStackTrace()
            } finally {
                isLoading = false
            }
        }
    }

    if (isLoading) {
        Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
            CircularProgressIndicator(color = MaterialTheme.colorScheme.primary)
        }
    } else {
        Column(modifier = Modifier.fillMaxSize()) {
            Text(
                text = "Phim Ngắn",
                style = MaterialTheme.typography.headlineSmall,
                fontWeight = FontWeight.Bold,
                modifier = Modifier.padding(16.dp)
            )
            LazyVerticalGrid(
                columns = GridCells.Fixed(3),
                contentPadding = PaddingValues(start = 8.dp, end = 8.dp, bottom = 16.dp),
                modifier = Modifier.fillMaxSize(),
                horizontalArrangement = Arrangement.spacedBy(8.dp),
                verticalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                items(movies) { movie ->
                    YoukuMovieCard(
                        movie = movie,
                        domain = domain,
                        modifier = Modifier.fillMaxWidth(),
                        onClick = { navController.navigate("movie_detail/${movie.slug}") }
                    )
                }
            }
        }
    }
}
