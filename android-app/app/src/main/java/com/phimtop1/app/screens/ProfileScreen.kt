package com.phimtop1.app.screens

import android.widget.Toast
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Person
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import com.phimtop1.app.api.LoginRequest
import com.phimtop1.app.api.RegisterRequest
import com.phimtop1.app.api.RetrofitClient
import com.phimtop1.app.utils.AuthManager
import kotlinx.coroutines.launch

@Composable
fun ProfileScreen() {
    val context = LocalContext.current
    val authManager = remember { AuthManager(context) }
    var isLoggedIn by remember { mutableStateOf(authManager.isLoggedIn()) }
    var isRegistering by remember { mutableStateOf(false) }
    
    if (isLoggedIn) {
        UserProfileView(authManager = authManager, onLogout = {
            authManager.clear()
            isLoggedIn = false
        })
    } else {
        if (isRegistering) {
            RegisterView(
                authManager = authManager,
                onRegisterSuccess = { isLoggedIn = true },
                onSwitchToLogin = { isRegistering = false }
            )
        } else {
            LoginView(
                authManager = authManager,
                onLoginSuccess = { isLoggedIn = true },
                onSwitchToRegister = { isRegistering = true }
            )
        }
    }
}

@Composable
fun UserProfileView(authManager: AuthManager, onLogout: () -> Unit) {
    val user = authManager.getUser()
    
    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(24.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Box(
            modifier = Modifier
                .size(100.dp)
                .clip(CircleShape)
                .background(MaterialTheme.colorScheme.primaryContainer),
            contentAlignment = Alignment.Center
        ) {
            Icon(Icons.Filled.Person, contentDescription = "Avatar", modifier = Modifier.size(60.dp), tint = MaterialTheme.colorScheme.onPrimaryContainer)
        }
        
        Spacer(modifier = Modifier.height(24.dp))
        
        Text(
            text = user?.name ?: "Người dùng",
            style = MaterialTheme.typography.headlineMedium,
            fontWeight = FontWeight.Bold
        )
        
        Spacer(modifier = Modifier.height(8.dp))
        
        Text(
            text = user?.email ?: "",
            style = MaterialTheme.typography.bodyLarge,
            color = Color.Gray
        )
        
        Spacer(modifier = Modifier.height(32.dp))
        
        Button(
            onClick = onLogout,
            colors = ButtonDefaults.buttonColors(containerColor = MaterialTheme.colorScheme.error)
        ) {
            Text("Đăng xuất")
        }
    }
}

@Composable
fun LoginView(authManager: AuthManager, onLoginSuccess: () -> Unit, onSwitchToRegister: () -> Unit) {
    val context = LocalContext.current
    val coroutineScope = rememberCoroutineScope()
    
    var email by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var isLoading by remember { mutableStateOf(false) }

    val apiKey = RetrofitClient.API_KEY

    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(24.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Text(
            text = "Đăng Nhập",
            style = MaterialTheme.typography.headlineLarge,
            fontWeight = FontWeight.Bold
        )
        Spacer(modifier = Modifier.height(8.dp))
        Text(
            text = "Đăng nhập để lưu lịch sử xem phim và bình luận.",
            style = MaterialTheme.typography.bodyMedium,
            color = Color.Gray
        )
        
        Spacer(modifier = Modifier.height(32.dp))
        
        OutlinedTextField(
            value = email,
            onValueChange = { email = it },
            label = { Text("Email") },
            modifier = Modifier.fillMaxWidth(),
            singleLine = true
        )
        
        Spacer(modifier = Modifier.height(16.dp))
        
        OutlinedTextField(
            value = password,
            onValueChange = { password = it },
            label = { Text("Mật khẩu") },
            modifier = Modifier.fillMaxWidth(),
            singleLine = true,
            visualTransformation = PasswordVisualTransformation()
        )
        
        Spacer(modifier = Modifier.height(32.dp))
        
        Button(
            onClick = {
                if (email.isBlank() || password.isBlank()) {
                    Toast.makeText(context, "Vui lòng nhập đầy đủ thông tin", Toast.LENGTH_SHORT).show()
                    return@Button
                }
                
                isLoading = true
                coroutineScope.launch {
                    try {
                        val response = RetrofitClient.instance.login(
                            apiKey = apiKey,
                            request = LoginRequest(email = email, password = password)
                        )
                        if (response.status == "success" && response.token != null) {
                            authManager.saveToken(response.token)
                            val user = response.user
                            if (user != null) {
                                authManager.saveUser(user.id, user.name, user.email, user.avatar)
                            }
                            onLoginSuccess()
                        } else {
                            Toast.makeText(context, response.message ?: "Đăng nhập thất bại", Toast.LENGTH_SHORT).show()
                        }
                    } catch (e: Exception) {
                        e.printStackTrace()
                        Toast.makeText(context, "Lỗi kết nối", Toast.LENGTH_SHORT).show()
                    } finally {
                        isLoading = false
                    }
                }
            },
            modifier = Modifier.fillMaxWidth().height(50.dp),
            enabled = !isLoading
        ) {
            if (isLoading) {
                CircularProgressIndicator(modifier = Modifier.size(24.dp), color = Color.White)
            } else {
                Text("Đăng nhập")
            }
        }
        
        Spacer(modifier = Modifier.height(16.dp))
        
        TextButton(onClick = onSwitchToRegister) {
            Text("Chưa có tài khoản? Đăng ký ngay", color = MaterialTheme.colorScheme.primary)
        }
    }
}

@Composable
fun RegisterView(authManager: AuthManager, onRegisterSuccess: () -> Unit, onSwitchToLogin: () -> Unit) {
    val context = LocalContext.current
    val coroutineScope = rememberCoroutineScope()
    
    var name by remember { mutableStateOf("") }
    var email by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var isLoading by remember { mutableStateOf(false) }

    val apiKey = RetrofitClient.API_KEY

    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(24.dp),
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Text(
            text = "Đăng Ký",
            style = MaterialTheme.typography.headlineLarge,
            fontWeight = FontWeight.Bold
        )
        Spacer(modifier = Modifier.height(8.dp))
        Text(
            text = "Tạo tài khoản mới.",
            style = MaterialTheme.typography.bodyMedium,
            color = Color.Gray
        )
        
        Spacer(modifier = Modifier.height(32.dp))
        
        OutlinedTextField(
            value = name,
            onValueChange = { name = it },
            label = { Text("Họ tên") },
            modifier = Modifier.fillMaxWidth(),
            singleLine = true
        )
        
        Spacer(modifier = Modifier.height(16.dp))
        
        OutlinedTextField(
            value = email,
            onValueChange = { email = it },
            label = { Text("Email") },
            modifier = Modifier.fillMaxWidth(),
            singleLine = true
        )
        
        Spacer(modifier = Modifier.height(16.dp))
        
        OutlinedTextField(
            value = password,
            onValueChange = { password = it },
            label = { Text("Mật khẩu") },
            modifier = Modifier.fillMaxWidth(),
            singleLine = true,
            visualTransformation = PasswordVisualTransformation()
        )
        
        Spacer(modifier = Modifier.height(32.dp))
        
        Button(
            onClick = {
                if (name.isBlank() || email.isBlank() || password.isBlank()) {
                    Toast.makeText(context, "Vui lòng nhập đầy đủ thông tin", Toast.LENGTH_SHORT).show()
                    return@Button
                }
                
                isLoading = true
                coroutineScope.launch {
                    try {
                        val response = RetrofitClient.instance.register(
                            apiKey = apiKey,
                            request = RegisterRequest(name = name, email = email, password = password)
                        )
                        if (response.status == "success" && response.token != null) {
                            authManager.saveToken(response.token)
                            val user = response.user
                            if (user != null) {
                                authManager.saveUser(user.id, user.name, user.email, user.avatar)
                            }
                            onRegisterSuccess()
                        } else {
                            Toast.makeText(context, response.message ?: "Đăng ký thất bại", Toast.LENGTH_SHORT).show()
                        }
                    } catch (e: Exception) {
                        e.printStackTrace()
                        Toast.makeText(context, "Lỗi kết nối", Toast.LENGTH_SHORT).show()
                    } finally {
                        isLoading = false
                    }
                }
            },
            modifier = Modifier.fillMaxWidth().height(50.dp),
            enabled = !isLoading
        ) {
            if (isLoading) {
                CircularProgressIndicator(modifier = Modifier.size(24.dp), color = Color.White)
            } else {
                Text("Đăng Ký")
            }
        }
        
        Spacer(modifier = Modifier.height(16.dp))
        
        TextButton(onClick = onSwitchToLogin) {
            Text("Đã có tài khoản? Đăng nhập", color = MaterialTheme.colorScheme.primary)
        }
    }
}
