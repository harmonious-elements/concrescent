<?php

function get_cart() {
	if (isset($_SESSION['cart'])) {
		return $_SESSION['cart'];
	} else {
		return array();
	}
}

function add_to_cart($item) {
	if (isset($_SESSION['cart'])) {
		$_SESSION['cart'][] = $item;
	} else {
		$_SESSION['cart'] = array($item);
	}
}

function get_from_cart($id) {
	if (isset($_SESSION['cart'])) {
		if ($id && isset($_SESSION['cart'][$id - 1])) {
			return $_SESSION['cart'][$id - 1];
		} else {
			return null;
		}
	} else {
		return null;
	}
}

function replace_in_cart($id, $item) {
	if (isset($_SESSION['cart'])) {
		if ($id && isset($_SESSION['cart'][$id - 1])) {
			$_SESSION['cart'][$id - 1] = $item;
		} else {
			$_SESSION['cart'][] = $item;
		}
	} else {
		$_SESSION['cart'] = array($item);
	}
}

function remove_from_cart($id) {
	if (isset($_SESSION['cart'])) {
		if ($id && isset($_SESSION['cart'][$id - 1])) {
			array_splice($_SESSION['cart'], $id - 1, 1);
		}
	}
}

function reset_promo_code() {
	if (isset($_SESSION['cart'])) {
		foreach ($_SESSION['cart'] as $id => $item) {
			$_SESSION['cart'][$id]['payment_promo_code'] = null;
			$_SESSION['cart'][$id]['payment_final_price'] = $item['payment_original_price'];
		}
	}
}

function destroy_cart() {
	unset($_SESSION['cart']);
	unset($_SESSION['cart_hash']);
	unset($_SESSION['cart_state']);
	session_destroy();
}