package com.phimtop1.app.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import coil.compose.AsyncImage
import com.phimtop1.app.api.ComicChapterData
import com.phimtop1.app.api.ComicDetailData
import com.phimtop1.app.api.RetrofitClient
import com.phimtop1.app.api.ToggleFollowRequest
import com.phimtop1.app.utils.AuthManager
import kotlinx.coroutines.launch
import android.widget.Toast
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.tooling.preview.Preview

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ComicDetailScreen(slug: String, onNavigateBack: () -> Unit, onReadChapter: (String) -> Unit) {
    val coroutineScope = rememberCoroutineScope()
    val context = LocalContext.current
    val authManager = remember { AuthManager(context) }
    var detailData by remember { mutableStateOf<ComicDetailData?>(null) }
    var isLoading by remember { mutableStateOf(true) }
    
    var isFollowing by remember { mutableStateOf(false) }
    var isFollowLoading by remember { mutableStateOf(false) }

    val apiKey = RetrofitClient.API_KEY

    LaunchedEffect(slug) {
        coroutineScope.launch {
            try {
                val response = RetrofitClient.instance.getComicDetail(apiKey = apiKey, slug = slug)
                if (response.status == "success") {
                    detailData = response.data
                }
            } catch (e: Exception) {
                e.printStackTrace()
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
                title = { Text(detailData?.comic?.name ?: "Chi Tiết Truyện", maxLines = 1, overflow = TextOverflow.Ellipsis) },
                navigationIcon = {
                    IconButton(onClick = onNavigateBack) {
                        Icon(Icons.Filled.ArrowBack, contentDescription = "Back")
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = Color.Transparent,
                    titleContentColor = Color.White
                )
            )
        }
    ) { paddingValues ->
        if (isLoading) {
            Box(modifier = Modifier.fillMaxSize().padding(paddingValues), contentAlignment = Alignment.Center) {
                CircularProgressIndicator()
            }
        } else if (detailData != null) {
            val comic = detailData!!.comic
            val domain = detailData!!.domain
            val thumb = comic.poster_url ?: comic.thumb_url ?: ""
            val imageUrl = if (thumb.startsWith("http")) thumb else if (thumb.startsWith("/")) "$domain$thumb" else "$domain/$thumb"

            LazyColumn(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(paddingValues),
                contentPadding = PaddingValues(bottom = 16.dp)
            ) {
                // Header Image
                item {
                    Box(modifier = Modifier.fillMaxWidth().height(300.dp)) {
                        AsyncImage(
                            model = imageUrl,
                            contentDescription = comic.name,
                            contentScale = ContentScale.Crop,
                            modifier = Modifier.fillMaxSize()
                        )
                        Box(
                            modifier = Modifier
                                .fillMaxSize()
                                .background(
                                    Brush.verticalGradient(
                                        colors = listOf(Color.Transparent, MaterialTheme.colorScheme.background),
                                        startY = 150f
                                    )
                                )
                        )
                    }
                }
                
                // Info Section
                item {
                    Column(modifier = Modifier.padding(16.dp)) {
                        Text(
                            text = comic.name,
                            style = MaterialTheme.typography.headlineMedium,
                            fontWeight = FontWeight.Bold
                        )
                        Spacer(modifier = Modifier.height(8.dp))
                        Text(
                            text = comic.content ?: "Đang cập nhật nội dung...",
                            style = MaterialTheme.typography.bodyMedium,
                            color = Color.LightGray
                        )
                        Spacer(modifier = Modifier.height(16.dp))
                        
                        Button(
                            onClick = {
                                if (!authManager.isLoggedIn()) {
                                    Toast.makeText(context, "Vui lòng đăng nhập để theo dõi", Toast.LENGTH_SHORT).show()
                                    return@Button
                                }
                                isFollowLoading = true
                                coroutineScope.launch {
                                    try {
                                        val token = authManager.getToken() ?: ""
                                        val res = RetrofitClient.instance.toggleFollow(
                                            apiKey, "Bearer $token", ToggleFollowRequest(
                                                item_slug = comic.slug,
                                                item_type = "comic",
                                                item_name = comic.name,
                                                thumb_url = comic.thumb_url
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
                            text = "Danh sách chương",
                            style = MaterialTheme.typography.titleLarge,
                            fontWeight = FontWeight.Bold
                        )
                        Spacer(modifier = Modifier.height(8.dp))
                    }
                }
                
                // Chapters List
                val chapters = detailData!!.chapters?.firstOrNull()?.server_data ?: emptyList()
                if (chapters.isEmpty()) {
                    item {
                        Text(
                            text = "Chưa có chương nào.",
                            modifier = Modifier.fillMaxWidth().padding(16.dp),
                            textAlign = TextAlign.Center,
                            color = Color.Gray
                        )
                    }
                } else {
                    items(chapters) { chapter ->
                        ChapterItem(chapter = chapter, onClick = { onReadChapter(chapter.chapter_api_data) })
                    }
                }
                
                item {
                    Spacer(modifier = Modifier.height(24.dp))
                    CommentSection(slug = comic.slug)
                }
            }
        } else {
            Box(modifier = Modifier.fillMaxSize().padding(paddingValues), contentAlignment = Alignment.Center) {
                Text("Không tìm thấy dữ liệu truyện.")
            }
        }
    }
}

@Composable
fun ChapterItem(chapter: ComicChapterData, onClick: () -> Unit) {
    Card(
        modifier = Modifier
            .fillMaxWidth()
            .padding(horizontal = 16.dp, vertical = 4.dp)
            .clickable { onClick() },
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surfaceVariant)
    ) {
        Row(
            modifier = Modifier.padding(16.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text(
                text = "Chương ${chapter.chapter_name}",
                style = MaterialTheme.typography.titleMedium,
                fontWeight = FontWeight.Medium,
                modifier = Modifier.weight(1f)
            )
        }
    }
}
