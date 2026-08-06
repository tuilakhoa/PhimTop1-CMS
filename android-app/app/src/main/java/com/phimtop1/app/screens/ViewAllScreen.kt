package com.phimtop1.app.screens

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.navigation.NavController
import com.phimtop1.app.api.MovieItem
import com.phimtop1.app.api.RetrofitClient
import kotlinx.coroutines.launch
import java.net.URLDecoder

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ViewAllScreen(navController: NavController, encodedTitle: String) {
    val title = try {
        URLDecoder.decode(encodedTitle, "UTF-8")
    } catch (e: Exception) {
        "Phim Mới"
    }

    val coroutineScope = rememberCoroutineScope()
    var movies by remember { mutableStateOf<List<MovieItem>>(emptyList()) }
    var domain by remember { mutableStateOf("") }
    var isLoading by remember { mutableStateOf(true) }

    val apiKey = RetrofitClient.API_KEY

    LaunchedEffect(Unit) {
        coroutineScope.launch {
            try {
                // Fetch page 1 of home to show a grid of items
                val response = RetrofitClient.instance.getHome(apiKey, page = 1)
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

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text(text = title, fontWeight = FontWeight.Bold) },
                navigationIcon = {
                    IconButton(onClick = { navController.popBackStack() }) {
                        Icon(Icons.Filled.ArrowBack, contentDescription = "Back", tint = Color.White)
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = MaterialTheme.colorScheme.background,
                    titleContentColor = Color.White
                )
            )
        }
    ) { padding ->
        Box(modifier = Modifier.padding(padding).fillMaxSize()) {
            if (isLoading) {
                CircularProgressIndicator(modifier = Modifier.align(Alignment.Center))
            } else {
                LazyVerticalGrid(
                    columns = GridCells.Fixed(3),
                    contentPadding = PaddingValues(bottom = 16.dp, start = 8.dp, end = 8.dp, top = 8.dp),
                    modifier = Modifier.fillMaxSize(),
                    verticalArrangement = Arrangement.spacedBy(8.dp),
                    horizontalArrangement = Arrangement.spacedBy(8.dp)
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
}
