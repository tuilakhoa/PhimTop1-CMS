package com.phimtop1.app.screens

import android.util.Base64
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.unit.dp
import coil.compose.AsyncImage
import com.phimtop1.app.api.OtruyenChapterData
import com.phimtop1.app.api.RetrofitClient
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ComicReadScreen(encodedUrl: String, onNavigateBack: () -> Unit) {
    val coroutineScope = rememberCoroutineScope()
    var chapterData by remember { mutableStateOf<OtruyenChapterData?>(null) }
    var isLoading by remember { mutableStateOf(true) }

    LaunchedEffect(encodedUrl) {
        coroutineScope.launch {
            try {
                val decodedUrl = String(Base64.decode(encodedUrl, Base64.URL_SAFE or Base64.NO_WRAP))
                val response = RetrofitClient.instance.getChapterImages(decodedUrl)
                if (response.status == "success") {
                    chapterData = response.data
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
                title = { Text("Đọc Truyện") },
                navigationIcon = {
                    IconButton(onClick = onNavigateBack) {
                        Icon(Icons.Filled.ArrowBack, contentDescription = "Back")
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = Color.Black.copy(alpha = 0.7f),
                    titleContentColor = Color.White
                )
            )
        }
    ) { paddingValues ->
        Box(
            modifier = Modifier
                .fillMaxSize()
                .background(Color.Black)
                .padding(paddingValues)
        ) {
            if (isLoading) {
                CircularProgressIndicator(modifier = Modifier.align(Alignment.Center))
            } else if (chapterData != null) {
                val cdn = chapterData!!.domain_cdn
                val path = chapterData!!.item.chapter_path
                val images = chapterData!!.item.chapter_image

                LazyColumn(modifier = Modifier.fillMaxSize()) {
                    items(images) { img ->
                        val imageUrl = "${cdn}/${path}/${img.image_file}"
                        AsyncImage(
                            model = imageUrl,
                            contentDescription = "Page ${img.image_page}",
                            contentScale = ContentScale.FillWidth,
                            modifier = Modifier.fillMaxWidth()
                        )
                    }
                }
            } else {
                Text(
                    text = "Không thể tải nội dung truyện.",
                    color = Color.White,
                    modifier = Modifier.align(Alignment.Center)
                )
            }
        }
    }
}
