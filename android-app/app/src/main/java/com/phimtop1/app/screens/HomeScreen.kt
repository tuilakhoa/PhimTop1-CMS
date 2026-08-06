package com.phimtop1.app.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.LazyRow
import androidx.compose.foundation.lazy.grid.GridCells
import androidx.compose.foundation.lazy.grid.LazyVerticalGrid
import androidx.compose.foundation.lazy.grid.items
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Search
import androidx.compose.material.icons.filled.Notifications
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.foundation.focusable
import androidx.compose.foundation.border
import androidx.compose.foundation.interaction.MutableInteractionSource
import androidx.compose.foundation.interaction.collectIsFocusedAsState
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavController
import coil.compose.AsyncImage
import com.phimtop1.app.api.CategoryItem
import com.phimtop1.app.api.MovieItem
import com.phimtop1.app.api.RetrofitClient
import androidx.compose.foundation.pager.HorizontalPager
import androidx.compose.foundation.pager.rememberPagerState
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch

@Composable
fun HomeScreen(navController: NavController) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
    ) {
        YoukuTopBar(navController)
        MoviesContent(navController)
    }
}



@Composable
fun MoviesContent(navController: NavController) {
    val coroutineScope = rememberCoroutineScope()
    var movies by remember { mutableStateOf<List<MovieItem>>(emptyList()) }
    var featuredMovies by remember { mutableStateOf<List<MovieItem>?>(null) }
    var featuredStyle by remember { mutableStateOf<String?>("single") }
    var categories by remember { mutableStateOf<List<CategoryItem>>(emptyList()) }
    var selectedCategory by remember { mutableStateOf("home") }
    var domain by remember { mutableStateOf("") }
    var isLoading by remember { mutableStateOf(true) }

    val apiKey = RetrofitClient.API_KEY

    LaunchedEffect(Unit) {
        coroutineScope.launch {
            try {
                val catResponse = RetrofitClient.instance.getCategories(apiKey)
                if (catResponse.status == "success") {
                    val catItems = mutableListOf(CategoryItem("home", "Trang chủ", "home"))
                    val hasPhimNgan = catResponse.data.items.any { it.slug == "phim-ngan" }
                    if (!hasPhimNgan) {
                        catItems.add(CategoryItem("phim-ngan", "Phim ngắn", "the-loai"))
                    }
                    catItems.addAll(catResponse.data.items.filter { it.slug != "phim-ngan" })
                    categories = catItems
                }
            } catch (e: Exception) {
                e.printStackTrace()
            }
        }
    }

    LaunchedEffect(selectedCategory, categories) {
        if (categories.isEmpty() && selectedCategory != "home") return@LaunchedEffect
        
        isLoading = true
        coroutineScope.launch {
            try {
                if (selectedCategory == "home") {
                    val response = RetrofitClient.instance.getHome(apiKey)
                    if (response.status == "success") {
                        movies = response.data.items
                        domain = response.data.domain
                        featuredMovies = response.data.featuredMovies
                        featuredStyle = response.data.featuredStyle
                    }
                } else {
                    val categoryType = categories.find { it.slug == selectedCategory }?.type ?: "the-loai"
                    val response = RetrofitClient.instance.getCategory(apiKey, categoryType, selectedCategory)
                    if (response.status == "success") {
                        movies = response.data.items
                        domain = response.data.domain
                    }
                }
            } catch (e: Exception) {
                e.printStackTrace()
            } finally {
                isLoading = false
            }
        }
    }
    
    Column(modifier = Modifier.fillMaxSize()) {
        AppCategoryTabs(categories, selectedCategory) { slug ->
            selectedCategory = slug
        }

        if (isLoading) {
            Box(modifier = Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                CircularProgressIndicator(color = MaterialTheme.colorScheme.primary)
            }
        } else {
            if (selectedCategory == "home") {
                val heroMovie = movies.firstOrNull()
                val remainingMovies = if (movies.isNotEmpty()) movies.drop(1) else emptyList()
                val chunks = remainingMovies.chunked(4)
                
                val sectionTitles = listOf(
                    "Đề cử cho bạn 🔥",
                    "Tình thân & tình yêu đan xen",
                    "Phim tình cảm đô thị",
                    "Cả nhà vui vẻ, cùng xem!",
                    "Bảng xếp hạng thịnh hành",
                    "Có thể bạn sẽ thích ✨",
                    "Khám phá thêm"
                )
                
                LazyColumn(
                    contentPadding = PaddingValues(bottom = 16.dp),
                    modifier = Modifier.fillMaxSize()
                ) {
                    if (!featuredMovies.isNullOrEmpty()) {
                        item {
                            HeroMovieSection(
                                movies = featuredMovies!!,
                                style = featuredStyle ?: "single",
                                domain = domain,
                                navController = navController
                            )
                        }
                    } else if (heroMovie != null) {
                        item {
                            HeroMovieSection(
                                movies = listOf(heroMovie),
                                style = "single",
                                domain = domain,
                                navController = navController
                            )
                        }
                    }
                    
                    chunks.forEachIndexed { index, chunk ->
                        val title = sectionTitles.getOrElse(index) { "Đang thịnh hành" }
                        item {
                            YoukuSection(
                                title = title, 
                                movies = chunk, 
                                domain = domain, 
                                navController = navController
                            )
                        }
                    }
                }
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

@Composable
fun YoukuTopBar(navController: NavController) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .padding(horizontal = 12.dp, vertical = 8.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        // Logo
        Text(
            text = "PhimTop1",
            color = MaterialTheme.colorScheme.primary,
            fontWeight = FontWeight.ExtraBold,
            fontSize = 20.sp,
            modifier = Modifier.padding(end = 12.dp)
        )

        // Search Bar
        val searchInteractionSource = remember { MutableInteractionSource() }
        val isSearchFocused by searchInteractionSource.collectIsFocusedAsState()

        Row(
            modifier = Modifier
                .weight(1f)
                .height(36.dp)
                .clip(RoundedCornerShape(18.dp))
                .background(if (isSearchFocused) Color.White.copy(alpha = 0.2f) else Color(0xFF252525))
                .border(
                    width = if (isSearchFocused) 2.dp else 0.dp,
                    color = if (isSearchFocused) MaterialTheme.colorScheme.primary else Color.Transparent,
                    shape = RoundedCornerShape(18.dp)
                )
                .clickable(
                    interactionSource = searchInteractionSource,
                    indication = null
                ) { navController.navigate("explore") }
                .focusable(interactionSource = searchInteractionSource)
                .padding(horizontal = 12.dp),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Icon(
                Icons.Filled.Search, 
                contentDescription = "Search", 
                tint = if (isSearchFocused) Color.White else Color.Gray, 
                modifier = Modifier.size(18.dp)
            )
            Spacer(modifier = Modifier.width(8.dp))
            Text(
                text = "Tìm kiếm phim, diễn viên, truyện tranh...",
                color = if (isSearchFocused) Color.White else Color.Gray,
                fontSize = 13.sp,
                maxLines = 1,
                overflow = TextOverflow.Ellipsis,
                modifier = Modifier.weight(1f)
            )
        }
        
        Spacer(modifier = Modifier.width(12.dp))
        
        // Bell Icon
        Icon(
            Icons.Filled.Notifications,
            contentDescription = "Notifications",
            tint = Color.White,
            modifier = Modifier
                .size(24.dp)
                .clickable { navController.navigate("notifications") }
        )
    }
}

@Composable
fun AppCategoryTabs(categories: List<CategoryItem>, selectedCategory: String, onSelect: (String) -> Unit) {
    LazyRow(
        modifier = Modifier
            .fillMaxWidth()
            .padding(horizontal = 4.dp, vertical = 4.dp),
        contentPadding = PaddingValues(horizontal = 8.dp)
    ) {
        if (categories.isEmpty()) {
            items(listOf("Trang chủ")) { tab ->
                val isSelected = true
                Text(
                    text = tab,
                    color = if (isSelected) Color.White else Color.Gray,
                    fontWeight = if (isSelected) FontWeight.Bold else FontWeight.Normal,
                    fontSize = if (isSelected) 16.sp else 14.sp,
                    modifier = Modifier.padding(horizontal = 12.dp, vertical = 8.dp)
                )
            }
        } else {
            items(categories) { category ->
                val isSelected = category.slug == selectedCategory
                val interactionSource = remember { MutableInteractionSource() }
                val isFocused by interactionSource.collectIsFocusedAsState()
                
                Column(
                    horizontalAlignment = Alignment.CenterHorizontally,
                    modifier = Modifier
                        .clickable { onSelect(category.slug) }
                        .focusable(interactionSource = interactionSource)
                        .padding(horizontal = 12.dp, vertical = 8.dp)
                ) {
                    Text(
                        text = category.name,
                        color = if (isSelected || isFocused) Color.White else Color.Gray,
                        fontWeight = if (isSelected) FontWeight.Bold else FontWeight.Normal,
                        fontSize = if (isSelected) 16.sp else 14.sp
                    )
                    if (isSelected) {
                        Box(
                            modifier = Modifier
                                .padding(top = 2.dp)
                                .width(20.dp)
                                .height(2.dp)
                                .background(MaterialTheme.colorScheme.primary)
                        )
                    }
                }
            }
        }
    }
}

@Composable
fun HeroMovieSection(movies: List<MovieItem>, style: String, domain: String, navController: NavController) {
    if (movies.isEmpty()) return

    if (style == "slider" && movies.size > 1) {
        val pagerState = rememberPagerState(pageCount = { movies.size })
        
        LaunchedEffect(pagerState.currentPage) {
            delay(4000)
            val nextPage = (pagerState.currentPage + 1) % movies.size
            pagerState.animateScrollToPage(nextPage)
        }

        HorizontalPager(
            state = pagerState,
            modifier = Modifier.fillMaxWidth()
        ) { page ->
            HeroMovieItem(movie = movies[page], domain = domain, navController = navController)
        }
    } else {
        HeroMovieItem(movie = movies.first(), domain = domain, navController = navController)
    }
}

@Composable
fun HeroMovieItem(movie: MovieItem, domain: String, navController: NavController) {
    val interactionSource = remember { MutableInteractionSource() }
    val isFocused by interactionSource.collectIsFocusedAsState()

    Box(
        modifier = Modifier
            .fillMaxWidth()
            .height(300.dp)
            .padding(horizontal = 12.dp, vertical = 8.dp)
            .clip(RoundedCornerShape(16.dp))
            .border(
                width = if (isFocused) 2.dp else 0.dp,
                color = if (isFocused) MaterialTheme.colorScheme.primary else Color.Transparent,
                shape = RoundedCornerShape(16.dp)
            )
            .clickable(
                interactionSource = interactionSource,
                indication = null
            ) { navController.navigate("movie_detail/${movie.slug}") }
            .focusable(interactionSource = interactionSource)
    ) {
        val thumb = movie.poster_url ?: movie.thumb_url ?: ""
        val imageUrl = if (thumb.startsWith("http")) thumb else if (thumb.startsWith("/")) "$domain$thumb" else "$domain/$thumb"

        AsyncImage(
            model = imageUrl,
            contentDescription = movie.name,
            contentScale = ContentScale.Crop,
            modifier = Modifier.fillMaxSize()
        )

        // Gradient overlay for text readability
        Box(
            modifier = Modifier
                .fillMaxSize()
                .background(
                    androidx.compose.ui.graphics.Brush.verticalGradient(
                        colors = listOf(Color.Transparent, Color.Black.copy(alpha = 0.9f)),
                        startY = 200f
                    )
                )
        )

        Column(
            modifier = Modifier
                .align(Alignment.BottomStart)
                .padding(16.dp)
        ) {
            Text(
                text = "ĐỘC QUYỀN",
                color = Color.White,
                fontSize = 10.sp,
                fontWeight = FontWeight.Bold,
                modifier = Modifier
                    .background(Color(0xFF8B1238), RoundedCornerShape(4.dp))
                    .padding(horizontal = 6.dp, vertical = 2.dp)
            )
            Spacer(modifier = Modifier.height(4.dp))
            Text(
                text = movie.name,
                color = Color.White,
                fontWeight = FontWeight.ExtraBold,
                fontSize = 26.sp,
                maxLines = 2,
                overflow = TextOverflow.Ellipsis
            )
            val subtitle = movie.origin_name ?: "Xem ngay bộ phim hot nhất"
            if (subtitle.isNotBlank()) {
                Text(
                    text = subtitle,
                    color = Color.LightGray,
                    fontSize = 14.sp,
                    modifier = Modifier.padding(top = 4.dp),
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis
                )
            }
            
            Row(modifier = Modifier.padding(top = 8.dp), verticalAlignment = Alignment.CenterVertically) {
                if (movie.year != null) {
                    Text(
                        text = "${movie.year}",
                        color = MaterialTheme.colorScheme.primary,
                        fontSize = 12.sp,
                        fontWeight = FontWeight.Bold,
                        modifier = Modifier
                            .background(MaterialTheme.colorScheme.primary.copy(alpha = 0.2f), RoundedCornerShape(4.dp))
                            .padding(horizontal = 6.dp, vertical = 2.dp)
                    )
                    Spacer(modifier = Modifier.width(8.dp))
                }
                Text(
                    text = "18+",
                    color = Color.Gray,
                    fontSize = 12.sp,
                    fontWeight = FontWeight.Bold,
                    modifier = Modifier
                        .background(Color(0xFF222222), RoundedCornerShape(4.dp))
                        .padding(horizontal = 6.dp, vertical = 2.dp)
                )
            }
            
            Row(modifier = Modifier.padding(top = 12.dp), verticalAlignment = Alignment.CenterVertically) {
                Button(
                    onClick = { navController.navigate("movie_detail/${movie.slug}") },
                    colors = ButtonDefaults.buttonColors(containerColor = MaterialTheme.colorScheme.primary),
                    shape = RoundedCornerShape(20.dp),
                    contentPadding = PaddingValues(horizontal = 16.dp, vertical = 8.dp)
                ) {
                    Text("▶ Xem ngay", fontWeight = FontWeight.Bold, fontSize = 14.sp)
                }
                Spacer(modifier = Modifier.width(12.dp))
                Box(
                    modifier = Modifier
                        .size(40.dp)
                        .background(Color(0xFF333333), RoundedCornerShape(20.dp)),
                    contentAlignment = Alignment.Center
                ) {
                    Text("+", color = Color.White, fontSize = 24.sp, modifier = Modifier.padding(bottom = 2.dp))
                }
            }
        }
    }
}

@Composable
fun YoukuSection(title: String, movies: List<MovieItem>, domain: String, navController: NavController) {
    Column(modifier = Modifier.padding(top = 16.dp)) {
        Row(
            modifier = Modifier.fillMaxWidth().padding(horizontal = 12.dp, vertical = 8.dp),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text(
                text = title,
                color = Color.White,
                fontWeight = FontWeight.Bold,
                fontSize = 18.sp
            )
            Text(
                text = "Xem tất cả >",
                color = Color.Gray,
                fontSize = 12.sp,
                modifier = Modifier
                    .clickable {
                        val encodedTitle = java.net.URLEncoder.encode(title, "UTF-8")
                        navController.navigate("view_all/$encodedTitle")
                    }
                    .padding(4.dp)
            )
        }
        
        LazyRow(
            contentPadding = PaddingValues(horizontal = 8.dp),
            horizontalArrangement = Arrangement.spacedBy(8.dp)
        ) {
            items(movies) { movie ->
                YoukuMovieCard(
                    movie = movie,
                    domain = domain,
                    onClick = { navController.navigate("movie_detail/${movie.slug}") }
                )
            }
        }
    }
}

@Composable
fun YoukuMovieCard(
    movie: MovieItem, 
    domain: String, 
    modifier: Modifier = Modifier.width(130.dp),
    onClick: () -> Unit
) {
    val interactionSource = remember { MutableInteractionSource() }
    val isFocused by interactionSource.collectIsFocusedAsState()

    Column(
        modifier = modifier
            .clip(RoundedCornerShape(8.dp))
            .background(if (isFocused) Color.White.copy(alpha = 0.1f) else Color.Transparent)
            .border(
                width = if (isFocused) 2.dp else 0.dp,
                color = if (isFocused) MaterialTheme.colorScheme.primary else Color.Transparent,
                shape = RoundedCornerShape(8.dp)
            )
            .clickable { onClick() }
            .focusable(interactionSource = interactionSource)
            .padding(4.dp)
    ) {
        val thumb = movie.thumb_url ?: movie.poster_url ?: ""
        val imageUrl = if (thumb.startsWith("http")) thumb else if (thumb.startsWith("/")) "$domain$thumb" else "$domain/$thumb"
        
        Box(
            modifier = Modifier
                .fillMaxWidth()
                .height(180.dp)
                .clip(RoundedCornerShape(8.dp))
                .background(Color(0xFF222222))
        ) {
            AsyncImage(
                model = imageUrl,
                contentDescription = movie.name,
                contentScale = ContentScale.Crop,
                modifier = Modifier.fillMaxSize()
            )
        }
        
        Spacer(modifier = Modifier.height(8.dp))
        
        Text(
            text = movie.name,
            color = Color.White,
            fontSize = 14.sp,
            maxLines = 1,
            overflow = TextOverflow.Ellipsis
        )
        
        val subtitle = movie.origin_name ?: "Đang cập nhật"
        if (subtitle.isNotBlank()) {
            Text(
                text = subtitle,
                color = Color.Gray,
                fontSize = 12.sp,
                maxLines = 1,
                overflow = TextOverflow.Ellipsis
            )
        }
    }
}
