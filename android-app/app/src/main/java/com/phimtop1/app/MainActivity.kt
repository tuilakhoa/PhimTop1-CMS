package com.phimtop1.app

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.*
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.navigation.NavHostController
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.NavType
import androidx.navigation.navArgument
import android.util.Base64
import androidx.navigation.compose.currentBackStackEntryAsState
import androidx.navigation.compose.rememberNavController
import androidx.compose.foundation.interaction.MutableInteractionSource
import androidx.compose.foundation.interaction.collectIsFocusedAsState
import androidx.compose.foundation.background
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.ui.draw.clip
import androidx.compose.ui.unit.dp
import com.phimtop1.app.api.RetrofitClient
import kotlinx.coroutines.launch
import com.phimtop1.app.screens.*

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContent {
            val youkuPink = Color(0xFFFF0055)
            val youkuBackground = Color(0xFF0F0F0F)
            val customColorScheme = darkColorScheme(
                primary = youkuPink,
                background = youkuBackground,
                surface = youkuBackground,
                surfaceVariant = Color(0xFF1F1F1F),
                onPrimary = Color.White
            )

            MaterialTheme(colorScheme = customColorScheme) {
                val navController = rememberNavController()
                
                Scaffold(
                    bottomBar = { BottomNavigationBar(navController) }
                ) { innerPadding ->
                    Box(modifier = Modifier.padding(innerPadding)) {
                        NavigationGraph(navController = navController)
                    }
                }
            }
        }
    }
}

@Composable
fun NavigationGraph(navController: NavHostController) {
    NavHost(navController, startDestination = "home") {
        composable("home") { HomeScreen(navController) }
        composable("explore") { ExploreScreen(navController) }
        composable(
            "search_result/{keyword}",
            arguments = listOf(navArgument("keyword") { type = NavType.StringType })
        ) { backStackEntry ->
            val keyword = backStackEntry.arguments?.getString("keyword") ?: ""
            ExploreScreen(navController = navController, initialKeyword = keyword)
        }
        composable("comics") { ComicsScreen(navController) }
        composable("shorts") { ShortsScreen(navController) }
        composable("follow") { FollowScreen(navController) }
        composable("notifications") { NotificationScreen(navController) }
        composable("profile") { ProfileScreen() }
        composable(
            "view_all/{title}",
            arguments = listOf(navArgument("title") { type = NavType.StringType })
        ) { backStackEntry ->
            val title = backStackEntry.arguments?.getString("title") ?: ""
            ViewAllScreen(navController = navController, encodedTitle = title)
        }
        composable(
            "movie_detail/{slug}",
            arguments = listOf(navArgument("slug") { type = NavType.StringType })
        ) { backStackEntry ->
            val slug = backStackEntry.arguments?.getString("slug") ?: ""
            MovieDetailScreen(
                slug = slug, 
                onNavigateBack = { navController.popBackStack() },
                onPlayVideo = { videoUrl ->
                    val encoded = Base64.encodeToString(videoUrl.toByteArray(), Base64.URL_SAFE or Base64.NO_WRAP)
                    navController.navigate("player/$encoded")
                },
                onActorClick = { actorName ->
                    navController.navigate("search_result/${android.net.Uri.encode(actorName)}")
                }
            )
        }
        composable(
            "player/{videoUrl}",
            arguments = listOf(navArgument("videoUrl") { type = NavType.StringType })
        ) { backStackEntry ->
            val encodedUrl = backStackEntry.arguments?.getString("videoUrl") ?: ""
            PlayerScreen(encodedUrl = encodedUrl)
        }
        composable(
            "comic_detail/{slug}",
            arguments = listOf(navArgument("slug") { type = NavType.StringType })
        ) { backStackEntry ->
            val slug = backStackEntry.arguments?.getString("slug") ?: ""
            ComicDetailScreen(
                slug = slug,
                onNavigateBack = { navController.popBackStack() },
                onReadChapter = { chapterUrl ->
                    val encoded = Base64.encodeToString(chapterUrl.toByteArray(), Base64.URL_SAFE or Base64.NO_WRAP)
                    navController.navigate("comic_read/$encoded")
                }
            )
        }
        composable(
            "comic_read/{chapterUrl}",
            arguments = listOf(navArgument("chapterUrl") { type = NavType.StringType })
        ) { backStackEntry ->
            val encodedUrl = backStackEntry.arguments?.getString("chapterUrl") ?: ""
            ComicReadScreen(
                encodedUrl = encodedUrl,
                onNavigateBack = { navController.popBackStack() }
            )
        }
    }
}

@Composable
fun BottomNavigationBar(navController: NavHostController) {
    val items = listOf(
        NavigationItem("home", "Trang chủ", Icons.Filled.Home),
        NavigationItem("explore", "Khám phá", Icons.Filled.Search),
        NavigationItem("shorts", "Phim ngắn", Icons.Filled.PlayArrow),
        NavigationItem("follow", "Theo dõi", Icons.Filled.Favorite),
        NavigationItem("profile", "Cá nhân", Icons.Filled.Person)
    )
    
    NavigationBar(
        containerColor = Color(0xFF151515),
        contentColor = Color.Gray
    ) {
        val navBackStackEntry by navController.currentBackStackEntryAsState()
        val currentRoute = navBackStackEntry?.destination?.route
        
        items.forEach { item ->
            val interactionSource = remember { MutableInteractionSource() }
            val isFocused by interactionSource.collectIsFocusedAsState()
            
            NavigationBarItem(
                modifier = Modifier
                    .clip(RoundedCornerShape(8.dp))
                    .background(if (isFocused) Color.White.copy(alpha = 0.1f) else Color.Transparent),
                icon = { Icon(item.icon, contentDescription = item.title) },
                label = { Text(item.title) },
                selected = currentRoute == item.route,
                interactionSource = interactionSource,
                colors = NavigationBarItemDefaults.colors(
                    selectedIconColor = MaterialTheme.colorScheme.primary,
                    unselectedIconColor = Color.Gray,
                    selectedTextColor = MaterialTheme.colorScheme.primary,
                    unselectedTextColor = Color.Gray,
                    indicatorColor = Color.Transparent
                ),
                onClick = {
                    navController.navigate(item.route) {
                        popUpTo(navController.graph.startDestinationId) {
                            // saveState = true causes confusing restoration of nested detail screens
                        }
                        launchSingleTop = true
                        // restoreState = true causes confusing restoration of nested detail screens
                    }
                }
            )
        }
    }
}

data class NavigationItem(val route: String, val title: String, val icon: androidx.compose.ui.graphics.vector.ImageVector)
