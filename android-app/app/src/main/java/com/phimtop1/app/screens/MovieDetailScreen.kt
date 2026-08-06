package com.phimtop1.app.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material.icons.filled.PlayArrow
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import coil.compose.AsyncImage
import com.phimtop1.app.api.MovieDetailData
import com.phimtop1.app.api.RetrofitClient
import com.phimtop1.app.api.ToggleFollowRequest
import com.phimtop1.app.utils.AuthManager
import kotlinx.coroutines.launch
import android.widget.Toast
import androidx.compose.ui.platform.LocalContext

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun MovieDetailScreen(slug: String, onNavigateBack: () -> Unit, onPlayVideo: (String) -> Unit, onActorClick: (String) -> Unit = {}) {
    val coroutineScope = rememberCoroutineScope()
    val context = LocalContext.current
    val authManager = remember { AuthManager(context) }
    var movieData by remember { mutableStateOf<MovieDetailData?>(null) }
    var isLoading by remember { mutableStateOf(true) }
    var errorMessage by remember { mutableStateOf<String?>(null) }
    
    var isFollowing by remember { mutableStateOf(false) }
    var isFollowLoading by remember { mutableStateOf(false) }

    val apiKey = RetrofitClient.API_KEY

    LaunchedEffect(slug) {
        coroutineScope.launch {
            try {
                val response = RetrofitClient.instance.getMovieDetail(apiKey, slug)
                if (response.status == "success") {
                    movieData = response.data
                } else {
                    errorMessage = "Lỗi khi lấy dữ liệu phim"
                }
            } catch (e: Exception) {
                e.printStackTrace()
                errorMessage = e.message
            } finally {
                isLoading = false
            }
            
            // Check follow status
            if (authManager.isLoggedIn()) {
                try {
                    val token = authManager.getToken() ?: ""
                    val followRes = RetrofitClient.instance.checkFollow(apiKey, "Bearer $token", slug)
                    if (followRes.status == "success") {
                        isFollowing = followRes.is_following
                    }
                } catch (e: Exception) {
                    e.printStackTrace()
                }
            }
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { },
                navigationIcon = {
                    IconButton(
                        onClick = onNavigateBack,
                        modifier = Modifier
                            .padding(8.dp)
                            .background(Color.Black.copy(alpha = 0.5f), shape = RoundedCornerShape(50))
                    ) {
                        Icon(Icons.Filled.ArrowBack, contentDescription = "Back", tint = Color.White)
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = Color.Transparent
                )
            )
        }
    ) { innerPadding ->
        Box(modifier = Modifier.fillMaxSize()) {
            if (isLoading) {
                CircularProgressIndicator(modifier = Modifier.align(Alignment.Center))
            } else if (errorMessage != null) {
                Text(
                    text = errorMessage!!,
                    color = MaterialTheme.colorScheme.error,
                    modifier = Modifier.align(Alignment.Center).padding(innerPadding)
                )
            } else if (movieData != null) {
                val data = movieData!!
                val movie = data.movie
                val domain = data.domain
                
                Column(
                    modifier = Modifier
                        .fillMaxSize()
                        .verticalScroll(rememberScrollState())
                ) {
                    // Immersive Header
                    Box(modifier = Modifier.fillMaxWidth().height(500.dp)) {
                        val thumb = movie.poster_url ?: movie.thumb_url ?: ""
                        val imageUrl = if (thumb.startsWith("http")) thumb else if (thumb.startsWith("/")) "$domain$thumb" else "$domain/$thumb"
                        
                        AsyncImage(
                            model = imageUrl,
                            contentDescription = movie.name,
                            contentScale = ContentScale.Crop,
                            modifier = Modifier.fillMaxSize()
                        )
                        
                        // Gradient Overlay
                        Box(
                            modifier = Modifier
                                .fillMaxSize()
                                .background(
                                    Brush.verticalGradient(
                                        colors = listOf(Color.Transparent, MaterialTheme.colorScheme.background),
                                        startY = 600f
                                    )
                                )
                        )
                        
                        // Play Button inside header (Optional)
                        if (!data.episodes.isNullOrEmpty() && data.episodes.first().server_data.isNotEmpty()) {
                            FloatingActionButton(
                                onClick = { onPlayVideo(data.episodes.first().server_data.first().link_m3u8) },
                                modifier = Modifier
                                    .align(Alignment.BottomEnd)
                                    .padding(24.dp),
                                containerColor = MaterialTheme.colorScheme.primary,
                                shape = RoundedCornerShape(50)
                            ) {
                                Icon(Icons.Filled.PlayArrow, contentDescription = "Phát phim", modifier = Modifier.size(32.dp))
                            }
                        }
                    }

                    // Content Section
                    Column(modifier = Modifier.padding(horizontal = 20.dp, vertical = 8.dp)) {
                        Text(
                            text = movie.name,
                            style = MaterialTheme.typography.headlineLarge,
                            fontWeight = FontWeight.ExtraBold
                        )
                        if (!movie.origin_name.isNullOrEmpty()) {
                            Text(
                                text = movie.origin_name,
                                style = MaterialTheme.typography.titleMedium,
                                color = MaterialTheme.colorScheme.onSurfaceVariant,
                                modifier = Modifier.padding(top = 4.dp)
                            )
                        }
                        
                        Spacer(modifier = Modifier.height(16.dp))
                        
                        // Tags Row
                        LazyRow(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                            if (movie.year != null) {
                                item { AssistChip(onClick = {}, label = { Text(movie.year.toString()) }) }
                            }
                            if (!movie.time.isNullOrEmpty()) {
                                item { AssistChip(onClick = {}, label = { Text(movie.time) }) }
                            }
                            if (!movie.episode_current.isNullOrEmpty()) {
                                item { AssistChip(onClick = {}, label = { Text(movie.episode_current) }) }
                            }
                        }
                        
                        Spacer(modifier = Modifier.height(16.dp))
                        
                        Button(
                            onClick = {
                                if (!authManager.isLoggedIn()) {
                                    Toast.makeText(context, "Vui lòng đăng nhập để theo dõi phim", Toast.LENGTH_SHORT).show()
                                    return@Button
                                }
                                isFollowLoading = true
                                coroutineScope.launch {
                                    try {
                                        val token = authManager.getToken() ?: ""
                                        val res = RetrofitClient.instance.toggleFollow(
                                            apiKey, "Bearer $token", ToggleFollowRequest(
                                                item_slug = movie.slug,
                                                item_type = "movie",
                                                item_name = movie.name,
                                                thumb_url = movie.thumb_url
                                            )
                                        )
                                        if (res.status == "success") {
                                            isFollowing = res.action == "added"
                                            Toast.makeText(context, if (isFollowing) "Đã thêm vào danh sách theo dõi" else "Đã bỏ theo dõi", Toast.LENGTH_SHORT).show()
                                        }
                                    } catch (e: Exception) {
                                        e.printStackTrace()
                                    } finally {
                                        isFollowLoading = false
                                    }
                                }
                            },
                            enabled = !isFollowLoading,
                            colors = ButtonDefaults.buttonColors(
                                containerColor = if (isFollowing) Color.DarkGray else MaterialTheme.colorScheme.primary
                            ),
                            modifier = Modifier.fillMaxWidth()
                        ) {
                            Text(if (isFollowing) "Hủy theo dõi" else "+ Theo dõi")
                        }

                        Spacer(modifier = Modifier.height(24.dp))
                        Text(
                            text = "Nội dung phim", 
                            style = MaterialTheme.typography.titleLarge, 
                            fontWeight = FontWeight.Bold
                        )
                        Spacer(modifier = Modifier.height(8.dp))
                        val contentClean = movie.content?.replace(Regex("<.*?>"), "")?.trim() ?: "Đang cập nhật..."
                        Text(
                            text = contentClean, 
                            style = MaterialTheme.typography.bodyLarge,
                            color = MaterialTheme.colorScheme.onSurfaceVariant,
                            lineHeight = MaterialTheme.typography.bodyLarge.lineHeight * 1.2
                        )
                        
                        // Actors
                        if (!movie.actor.isNullOrEmpty()) {
                            Spacer(modifier = Modifier.height(24.dp))
                            Text(
                                text = "Diễn viên", 
                                style = MaterialTheme.typography.titleLarge, 
                                fontWeight = FontWeight.Bold
                            )
                            Spacer(modifier = Modifier.height(8.dp))
                            LazyRow(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                                items(movie.actor) { actorName ->
                                    OutlinedButton(onClick = { onActorClick(actorName) }) {
                                        Text(actorName)
                                    }
                                }
                            }
                        }

                        // Episodes
                        if (!data.episodes.isNullOrEmpty()) {
                            Spacer(modifier = Modifier.height(32.dp))
                            Text(
                                text = "Tập phim", 
                                style = MaterialTheme.typography.titleLarge, 
                                fontWeight = FontWeight.Bold
                            )
                            Spacer(modifier = Modifier.height(8.dp))
                            
                            data.episodes.forEach { episodeGroup ->
                                Text(
                                    text = episodeGroup.server_name,
                                    style = MaterialTheme.typography.labelLarge,
                                    color = MaterialTheme.colorScheme.primary,
                                    modifier = Modifier.padding(vertical = 8.dp)
                                )
                                LazyRow(
                                    horizontalArrangement = Arrangement.spacedBy(12.dp),
                                    contentPadding = PaddingValues(vertical = 8.dp)
                                ) {
                                    items(episodeGroup.server_data) { episode ->
                                        OutlinedButton(
                                            onClick = { onPlayVideo(episode.link_m3u8) },
                                            shape = RoundedCornerShape(8.dp)
                                        ) {
                                            Text(episode.name, fontWeight = FontWeight.Bold)
                                        }
                                    }
                                }
                            }
                        }
                        
                        Spacer(modifier = Modifier.height(24.dp))
                        
                        CommentSection(slug = movie.slug)
                        
                        Spacer(modifier = Modifier.height(40.dp))
                    }
                }
            }
        }
    }
}
