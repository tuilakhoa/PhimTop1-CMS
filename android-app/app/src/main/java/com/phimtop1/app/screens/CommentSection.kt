package com.phimtop1.app.screens

import android.widget.Toast
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.Send
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import com.phimtop1.app.api.CommentItem
import com.phimtop1.app.api.PostCommentRequest
import com.phimtop1.app.api.RetrofitClient
import com.phimtop1.app.utils.AuthManager
import kotlinx.coroutines.launch

@Composable
fun CommentSection(slug: String) {
    val context = LocalContext.current
    val authManager = remember { AuthManager(context) }
    val coroutineScope = rememberCoroutineScope()
    
    var comments by remember { mutableStateOf<List<CommentItem>>(emptyList()) }
    var isLoading by remember { mutableStateOf(true) }
    var commentText by remember { mutableStateOf("") }
    var isSubmitting by remember { mutableStateOf(false) }
    var isAnonymous by remember { mutableStateOf(true) }
    
    val user = authManager.getUser()
    val token = authManager.getToken() ?: ""
    val isLoggedIn = authManager.isLoggedIn()

    LaunchedEffect(isLoggedIn) {
        if (isLoggedIn) {
            isAnonymous = false
        }
    }

    // Fetch comments
    fun fetchComments() {
        coroutineScope.launch {
            try {
                val response = RetrofitClient.instance.getComments(RetrofitClient.API_KEY, slug)
                if (response.success) {
                    comments = response.data ?: emptyList()
                }
            } catch (e: Exception) {
                e.printStackTrace()
            } finally {
                isLoading = false
            }
        }
    }

    LaunchedEffect(slug) {
        fetchComments()
    }

    Column(
        modifier = Modifier
            .fillMaxWidth()
            .padding(16.dp)
    ) {
        Text(
            text = "Bình luận (${comments.size})",
            style = MaterialTheme.typography.titleLarge,
            fontWeight = FontWeight.Bold,
            color = Color.White
        )
        Spacer(modifier = Modifier.height(16.dp))
        
        // Input section
        Row(
            modifier = Modifier.fillMaxWidth(),
            verticalAlignment = Alignment.Top
        ) {
            Box(
                modifier = Modifier
                    .size(40.dp)
                    .clip(CircleShape)
                    .background(MaterialTheme.colorScheme.primaryContainer),
                contentAlignment = Alignment.Center
            ) {
                Icon(Icons.Filled.Person, contentDescription = "Avatar", tint = MaterialTheme.colorScheme.onPrimaryContainer)
            }
            Spacer(modifier = Modifier.width(12.dp))
            Column(modifier = Modifier.weight(1f)) {
                OutlinedTextField(
                    value = commentText,
                    onValueChange = { commentText = it },
                    placeholder = { Text("Nhập bình luận của bạn...") },
                    modifier = Modifier.fillMaxWidth(),
                    colors = OutlinedTextFieldDefaults.colors(
                        focusedContainerColor = Color(0xFF1E1E1E),
                        unfocusedContainerColor = Color(0xFF1E1E1E),
                        focusedBorderColor = MaterialTheme.colorScheme.primary,
                        unfocusedBorderColor = Color.Transparent
                    ),
                    shape = RoundedCornerShape(16.dp),
                    maxLines = 3
                )
                
                Spacer(modifier = Modifier.height(8.dp))
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Checkbox(
                        checked = isAnonymous,
                        onCheckedChange = { isAnonymous = it }
                    )
                    Text("Bình luận ẩn danh", color = Color.Gray, style = MaterialTheme.typography.bodySmall)
                }
            }
            Spacer(modifier = Modifier.width(12.dp))
            IconButton(
                onClick = {
                    if (commentText.isBlank()) return@IconButton
                    if (isSubmitting) return@IconButton
                    
                    isSubmitting = true
                    coroutineScope.launch {
                        try {
                            val request = PostCommentRequest(
                                slug = slug,
                                name = if (isAnonymous) "Ẩn danh" else (user?.name ?: "Người dùng"),
                                content = commentText,
                                anonymous = isAnonymous
                            )
                            val response = RetrofitClient.instance.postComment(
                                RetrofitClient.API_KEY, 
                                "Bearer $token",
                                request
                            )
                            if (response.success) {
                                commentText = ""
                                fetchComments()
                                Toast.makeText(context, "Bình luận thành công", Toast.LENGTH_SHORT).show()
                            } else {
                                Toast.makeText(context, response.message ?: "Lỗi", Toast.LENGTH_SHORT).show()
                            }
                        } catch (e: Exception) {
                            e.printStackTrace()
                            Toast.makeText(context, "Lỗi kết nối", Toast.LENGTH_SHORT).show()
                        } finally {
                            isSubmitting = false
                        }
                    }
                },
                modifier = Modifier
                    .size(48.dp)
                    .clip(CircleShape)
                    .background(MaterialTheme.colorScheme.primary)
            ) {
                if (isSubmitting) {
                    CircularProgressIndicator(modifier = Modifier.size(24.dp), color = Color.White, strokeWidth = 2.dp)
                } else {
                    Icon(Icons.Filled.Send, contentDescription = "Send", tint = Color.White)
                }
            }
        }
        
        Spacer(modifier = Modifier.height(24.dp))
        
        // Comment list
        if (isLoading) {
            CircularProgressIndicator(modifier = Modifier.align(Alignment.CenterHorizontally))
        } else if (comments.isEmpty()) {
            Text(
                "Chưa có bình luận nào. Hãy là người đầu tiên!",
                color = Color.Gray,
                modifier = Modifier.align(Alignment.CenterHorizontally)
            )
        } else {
            Column(modifier = Modifier.fillMaxWidth()) {
                comments.forEach { comment ->
                    CommentItemView(comment)
                }
            }
        }
    }
}

@Composable
fun CommentItemView(comment: CommentItem) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .padding(vertical = 8.dp)
    ) {
        Box(
            modifier = Modifier
                .size(40.dp)
                .clip(CircleShape)
                .background(Color(0xFF2A2A2A)),
            contentAlignment = Alignment.Center
        ) {
            Icon(Icons.Filled.Person, contentDescription = "Avatar", tint = Color.Gray)
        }
        Spacer(modifier = Modifier.width(12.dp))
        Column {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Text(
                    text = comment.user_name,
                    fontWeight = FontWeight.Bold,
                    color = Color.White,
                    style = MaterialTheme.typography.bodyMedium
                )
                Spacer(modifier = Modifier.width(8.dp))
                Text(
                    text = comment.time_ago,
                    color = Color.Gray,
                    style = MaterialTheme.typography.bodySmall
                )
            }
            Spacer(modifier = Modifier.height(4.dp))
            Text(
                text = comment.content,
                color = Color(0xFFE0E0E0),
                style = MaterialTheme.typography.bodyMedium
            )
        }
    }
}
