<?php

/**
 * Persian Calendar Layout Component
 * 
 * This file contains the HTML structure and CSS styling for the Persian calendar modal.
 * It can be included in any page that needs calendar functionality.
 */
?>

<!-- Calendar Modal -->
<div id="calendar-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-96 max-w-full mx-4">
        <!-- Calendar Header -->
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800">تقویم</h3>
            <button id="close-calendar" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Calendar Navigation -->
        <div class="flex items-center justify-between mb-4">
            <button id="prev-month" class="p-2 hover:bg-gray-100 rounded">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
            <h4 id="current-month-year" class="text-lg font-bold text-gray-800">خرداد 1403</h4>
            <button id="next-month" class="p-2 hover:bg-gray-100 rounded">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
        </div>

        <!-- Calendar Grid -->
        <div id="calendar-grid" class="grid grid-cols-7 gap-1 mb-4">
            <!-- Days of week header -->
            <div class="text-center text-sm font-medium text-gray-600 py-2">شنبه</div>
            <div class="text-center text-sm font-medium text-gray-600 py-2">یکشنبه</div>
            <div class="text-center text-sm font-medium text-gray-600 py-2">دوشنبه</div>
            <div class="text-center text-sm font-medium text-gray-600 py-2">سه‌شنبه</div>
            <div class="text-center text-sm font-medium text-gray-600 py-2">چهارشنبه</div>
            <div class="text-center text-sm font-medium text-gray-600 py-2">پنج‌شنبه</div>
            <div class="text-center text-sm font-medium text-gray-600 py-2">جمعه</div>
            <!-- Calendar days will be inserted here -->
        </div>

        <!-- Selected Date Range Display -->
        <div class="mb-4 p-3 bg-gray-50 rounded-lg">
            <div class="text-sm text-gray-600 mb-1 text-center">بازه انتخاب شده:</div>
            <div id="selected-range" class="font-medium text-gray-800 text-center">تاریخی انتخاب نشده</div>
        </div>

        <!-- Calendar Actions -->
        <div class="flex gap-2">
            <button id="apply-date-range" class="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 disabled:bg-gray-300" disabled>
                اعمال
            </button>
            <button id="clear-selection" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400">
                پاک کردن
            </button>
        </div>
    </div>
</div>

<style>
    /* Calendar modal styling */
    .calendar-day {
        text-align: center;
        padding: 8px 4px;
        cursor: pointer;
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .calendar-day:hover {
        background-color: #f3f4f6;
    }

    .calendar-day.selected-start {
        background-color: #f97316;
        color: white;
        padding: 4px;
        border-radius: 8px;
    }

    .calendar-day.selected-end {
        background-color: #f97316;
        color: white;
        padding: 4px;
        border-radius: 8px;
    }

    .calendar-day.in-range {
        background-color: #fde68a;
    }

    .calendar-day.other-month {
        color: #9ca3af;
    }

    .calendar-day.today {
        border: 2px solid #ef4444;
    }

    .calendar-day.selected {
        background-color: #f97316;
        color: white;
        padding: 4px;
        border-radius: 8px;
    }
</style>