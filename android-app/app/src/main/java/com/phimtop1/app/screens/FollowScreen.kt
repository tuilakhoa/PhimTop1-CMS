package com.phimtop1.app.screens

import androidx.compose.foundation.clickable
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
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavController
import coil.compose.AsyncImage
import com.phimtop1.app.api.FollowItem
import com.phimtop1.app.api.RetrofitClient
import com.phimtop1.app.utils.AuthManager
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun FollowScreen(navController: NavController) {
    val context = LocalContext.current
    val authManager = remember { AuthManager(context) }
    val coroutineScope = rememberCoroutineScope()
    
    var follows by remember { mutableStateOf<List<FollowItem>>(emptyList()) }
    var isLoading by remember { mutableStateOf(false) }
    var errorMessage by remember { mutableStateOf<String?>(null) }
    val apiKey = RetrofitClient.API_KEY

    LaunchedEffect(authManager.isLoggedIn()) {
        if (!authManager.isLoggedIn()) {
            errorMessage = "Vui lòng đăng nhập để xem danh sách theo dõi."
            return@LaunchedEffect
        }
        
        isLoading = true
        errorMessage = null
        val token = authManager.getToken() ?: ""
        
        coroutineScope.launch {
            try {
                val response = RetrofitClient.instance.getFollows(apiKey, "Bearer $token", "movie")
                if (response.status == "success") {
                    follows = response.data
                } else {
                    errorMessage = "Không thể lấy dữ liệu."
                }
            } catch (e: Exception) {
                errorMessage = "Lỗi kết nối: ${e.message}"
            } finally {
                isLoading = false
            }
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Đang Theo Dõi", fontWeight = FontWeight.Bold) },
                colors = TopAppBarDefaults.topAppBarColors(
                    containerColor = MaterialTheme.colorScheme.background,
                    titleContentColor = Color.White
                )
            )
        }
    ) { padding ->
        Column(modifier = Modifier.padding(padding).fillMaxSize()) {
            if (!authManager.isLoggedIn()) {
                Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                    Column(horizontalAlignment = Alignment.CenterHorizontally) {
                        Text(errorMessage ?: "", color = Color.Gray)
                        Spacer(modifier = Modifier.height(16.dp))
                        Button(onClick = { navController.navigate("profile") }) { // Profile screen will prompt login
                            Text("Đăng nhập")
                        }
                    }
                }
            } else if (isLoading) {
                Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                    CircularProgressIndicator()
                }
            } else if (follows.isEmpty()) {
                Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                    Text("Bạn chưa theo dõi nội dung nào.", color = Color.Gray)
                }
            } else {
                LazyVerticalGrid(
                    columns = GridCells.Fixed(3),
                    contentPadding = PaddingValues(8.dp),
                    modifier = Modifier.fillMaxSize()
                ) {
                    items(follows) { item ->
                        FollowCard(item = item) {
                            if (item.item_type == "movie") {
                                navController.navigate("movie_detail/${item.item_slug}")
                            } else {
                                navController.navigate("comic_detail/${item.item_slug}")
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
fun FollowCard(item: FollowItem, onClick: () -> Unit) {
    Card(
        modifier = Modifier
            .padding(4.dp)
            .fillMaxWidth()
            .clickable { onClick() },
        shape = MaterialTheme.shapes.small
    ) {
        Column {
            AsyncImage(
                model = item.thumb_url,
                contentDescription = item.item_name,
                contentScale = ContentScale.Crop,
                modifier = Modifier
                    .fillMaxWidth()
                    .height(150.dp)
            )
            Text(
                text = item.item_name,
                modifier = Modifier.padding(8.dp),
                maxLines = 2,
                overflow = TextOverflow.Ellipsis,
                fontSize = 12.sp,
                color = Color.White
            )
        }
    }
}
