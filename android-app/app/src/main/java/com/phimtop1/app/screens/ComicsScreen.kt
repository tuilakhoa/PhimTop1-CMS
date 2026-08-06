package com.phimtop1.app.screens

import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.navigation.NavController
import coil.compose.AsyncImage
import com.phimtop1.app.api.MovieItem
import com.phimtop1.app.api.RetrofitClient
import kotlinx.coroutines.launch

@Composable
fun ComicsScreen(navController: NavController) {
    val coroutineScope = rememberCoroutineScope()
    var comics by remember { mutableStateOf<List<MovieItem>>(emptyList()) }
    var domain by remember { mutableStateOf("") }
    var isLoading by remember { mutableStateOf(true) }

    val apiKey = RetrofitClient.API_KEY

    LaunchedEffect(Unit) {
        coroutineScope.launch {
            try {
                val response = RetrofitClient.instance.getComics(apiKey)
                if (response.status == "success") {
                    comics = response.data.items
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
            CircularProgressIndicator()
        }
    } else {
        Column(modifier = Modifier.fillMaxSize()) {
            Text(
                text = "Truyện Tranh Mới",
                style = MaterialTheme.typography.headlineSmall,
                fontWeight = FontWeight.Bold,
                modifier = Modifier.padding(16.dp)
            )
            LazyVerticalGrid(
                columns = GridCells.Fixed(3),
                contentPadding = PaddingValues(start = 8.dp, end = 8.dp, bottom = 16.dp),
                modifier = Modifier.fillMaxSize()
            ) {
                items(comics) { comic ->
                    ComicCard(
                        comic = comic,
                        domain = domain,
                        onClick = { navController.navigate("comic_detail/${comic.slug}") }
                    )
                }
            }
        }
    }
}

@Composable
fun ComicCard(comic: MovieItem, domain: String, onClick: () -> Unit) {
    Card(
        shape = RoundedCornerShape(8.dp),
        elevation = CardDefaults.cardElevation(defaultElevation = 4.dp),
        modifier = Modifier
            .padding(8.dp)
            .fillMaxWidth()
            .height(200.dp)
            .clickable { onClick() }
    ) {
        Column {
            val thumb = comic.thumb_url ?: comic.poster_url ?: ""
            val imageUrl = if (thumb.startsWith("http")) thumb else if (thumb.startsWith("/")) "$domain$thumb" else "$domain/$thumb"
            
            AsyncImage(
                model = imageUrl,
                contentDescription = comic.name,
                contentScale = ContentScale.Crop,
                modifier = Modifier
                    .fillMaxWidth()
                    .weight(1f)
            )
            Text(
                text = comic.name,
                style = MaterialTheme.typography.bodySmall,
                fontWeight = FontWeight.Medium,
                maxLines = 2,
                overflow = TextOverflow.Ellipsis,
                modifier = Modifier.padding(8.dp)
            )
        }
    }
}
