import 'package:flutter/material.dart';

class FocusableWrapper extends StatefulWidget {
  final Widget child;
  final VoidCallback onTap;
  final Function(bool)? onFocus;
  final double scaleOnFocus;

  const FocusableWrapper({
    Key? key,
    required this.child,
    required this.onTap,
    this.onFocus,
    this.scaleOnFocus = 1.08,
  }) : super(key: key);

  @override
  State<FocusableWrapper> createState() => _FocusableWrapperState();
}

class _FocusableWrapperState extends State<FocusableWrapper> {
  bool _isFocused = false;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onFocusChange: (hasFocus) {
          setState(() {
            _isFocused = hasFocus;
          });
          if (widget.onFocus != null) {
            widget.onFocus!(hasFocus);
          }
        },
        onTap: widget.onTap,
        borderRadius: BorderRadius.circular(12),
        focusColor: Colors.transparent,
        hoverColor: Colors.transparent,
        highlightColor: Colors.transparent,
        splashColor: Colors.transparent,
        child: AnimatedScale(
          scale: _isFocused ? widget.scaleOnFocus : 1.0,
          duration: const Duration(milliseconds: 250),
          curve: Curves.easeOutCubic,
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 250),
            curve: Curves.easeOutCubic,
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: _isFocused ? Colors.white : Colors.transparent,
                width: _isFocused ? 3 : 0,
              ),
              boxShadow: _isFocused
                  ? [
                      BoxShadow(
                        color: Colors.white.withOpacity(0.3),
                        blurRadius: 15,
                        spreadRadius: 2,
                        offset: const Offset(0, 8),
                      ),
                      BoxShadow(
                        color: Colors.black.withOpacity(0.5),
                        blurRadius: 10,
                        spreadRadius: -2,
                        offset: const Offset(0, 10),
                      )
                    ]
                  : null,
            ),
            child: widget.child,
          ),
        ),
      ),
    );
  }
}
