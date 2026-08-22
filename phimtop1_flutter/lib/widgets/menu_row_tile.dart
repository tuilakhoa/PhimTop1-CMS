import 'package:flutter/material.dart';

class MenuRowTile extends StatelessWidget {
  final IconData icon;
  final String title;
  final VoidCallback? onTap;
  final Color iconColor;
  final Color textColor;
  final String? subtitle;
  final Widget? trailing;
  final bool showTrailing;
  final Color? hintColor;

  const MenuRowTile({
    super.key,
    required this.icon,
    required this.title,
    this.onTap,
    required this.iconColor,
    required this.textColor,
    this.subtitle,
    this.trailing,
    this.showTrailing = true,
    this.hintColor,
  });

  @override
  Widget build(BuildContext context) {
    final effectiveHintColor = hintColor ?? (Theme.of(context).brightness == Brightness.dark ? Colors.grey : Colors.grey[700]);

    return ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
      leading: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          color: iconColor.withOpacity(0.15),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Icon(icon, color: iconColor, size: 22),
      ),
      title: Text(title, style: TextStyle(color: textColor, fontSize: 16, fontWeight: FontWeight.w500)),
      subtitle: subtitle != null ? Text(subtitle!, style: TextStyle(color: effectiveHintColor, fontSize: 12)) : null,
      trailing: trailing ?? (showTrailing ? Icon(Icons.chevron_right, color: effectiveHintColor, size: 20) : null),
      onTap: onTap,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
    );
  }
}
