package com.phimtop1.app.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material.icons.filled.Notifications
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavController
import com.phimtop1.app.api.MarkReadRequest
import com.phimtop1.app.api.NotificationItem
import com.phimtop1.app.api.RetrofitClient
import com.phimtop1.app.utils.AuthManager
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun NotificationScreen(navController: NavController) {
    val context = LocalContext.current
    val authManager = remember { AuthManager(context) }
    val coroutineScope = rememberCoroutineScope()
    
    var notifications by remember { mutableStateOf<List<NotificationItem>>(emptyList()) }
    var isLoading by remember { mutableStateOf(false) }
    var errorMessage by remember { mutableStateOf<String?>(null) }

    val apiKey = RetrofitClient.API_KEY

    LaunchedEffect(authManager.isLoggedIn()) {
        if (!authManager.isLoggedIn()) {
            errorMessage = "Vui lòng đăng nhập để xem thông báo."
            return@LaunchedEffect
        }
        
        isLoading = true
        errorMessage = null
        val token = authManager.getToken() ?: ""
        
        coroutineScope.launch {
            try {
                val response = RetrofitClient.instance.getNotifications(apiKey, "Bearer $token")
                if (response.status == "success") {
                    notifications = response.data
                } else {
                    errorMessage = "Không thể lấy thông báo."
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
                title = { Text("Thông Báo", fontWeight = FontWeight.Bold) },
                navigationIcon = {
                    IconButton(onClick = { navController.popBackStack() }) {
                        Icon(Icons.Filled.ArrowBack, contentDescription = "Back")
                    }
                },
                actions = {
                    if (authManager.isLoggedIn() && notifications.any { it.is_read == 0 }) {
                        TextButton(onClick = {
                            coroutineScope.launch {
                                val token = authManager.getToken() ?: ""
                                try {
                                    RetrofitClient.instance.markNotificationsRead(apiKey, "Bearer $token", MarkReadRequest(0))
                                    notifications = notifications.map { it.copy(is_read = 1) }
                                } catch (e: Exception) {
                                    e.printStackTrace()
                                }
                            }
                        }) {
                            Text("Đọc tất cả", color = MaterialTheme.colorScheme.primary)
                        }
                    }
                },
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
                        Button(onClick = { navController.navigate("profile") }) {
                            Text("Đăng nhập")
                        }
                    }
                }
            } else if (isLoading) {
                Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                    CircularProgressIndicator()
                }
            } else if (notifications.isEmpty()) {
                Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                    Text("Không có thông báo nào.", color = Color.Gray)
                }
            } else {
                LazyColumn(modifier = Modifier.fillMaxSize()) {
                    items(notifications) { item ->
                        NotificationCard(item = item, onClick = {
                            if (item.is_read == 0) {
                                coroutineScope.launch {
                                    val token = authManager.getToken() ?: ""
                                    try {
                                        RetrofitClient.instance.markNotificationsRead(apiKey, "Bearer $token", MarkReadRequest(item.id))
                                        notifications = notifications.map { if (it.id == item.id) it.copy(is_read = 1) else it }
                                    } catch (e: Exception) {
                                        e.printStackTrace()
                                    }
                                }
                            }
                        })
                    }
                }
            }
        }
    }
}

@Composable
fun NotificationCard(item: NotificationItem, onClick: () -> Unit) {
    Card(
        modifier = Modifier
            .padding(horizontal = 16.dp, vertical = 8.dp)
            .fillMaxWidth()
            .clickable { onClick() },
        colors = CardDefaults.cardColors(
            containerColor = if (item.is_read == 0) MaterialTheme.colorScheme.primary.copy(alpha = 0.1f) else Color(0xFF222222)
        )
    ) {
        Row(modifier = Modifier.padding(16.dp), verticalAlignment = Alignment.CenterVertically) {
            Box(
                modifier = Modifier
                    .size(48.dp)
                    .background(MaterialTheme.colorScheme.primary.copy(alpha = 0.2f), shape = MaterialTheme.shapes.small),
                contentAlignment = Alignment.Center
            ) {
                Icon(Icons.Filled.Notifications, contentDescription = null, tint = MaterialTheme.colorScheme.primary)
            }
            Spacer(modifier = Modifier.width(16.dp))
            Column(modifier = Modifier.weight(1f)) {
                Text(
                    text = item.title,
                    fontWeight = if (item.is_read == 0) FontWeight.Bold else FontWeight.Normal,
                    fontSize = 16.sp,
                    color = Color.White
                )
                Spacer(modifier = Modifier.height(4.dp))
                Text(
                    text = item.message,
                    fontSize = 14.sp,
                    color = Color.LightGray
                )
                Spacer(modifier = Modifier.height(4.dp))
                Text(
                    text = item.created_at,
                    fontSize = 12.sp,
                    color = Color.Gray
                )
            }
        }
    }
}
