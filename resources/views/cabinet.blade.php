@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 text-white">
    <div class="bg-[#1e1e2f] p-6 rounded-2xl shadow-md max-w-xl mx-auto">
        <div class="flex items-center space-x-4 mb-6">
            <img src="{{ Auth::user()->avatar }}" alt="Avatar" class="w-16 h-16 rounded-full border-2 border-gray-500">
            <div>
                <h2 class="text-2xl font-bold">{{ Auth::user()->name }}</h2>
                <p class="text-gray-400">@{{ Auth::user()->username }}</p>
            </div>
        </div>

        <div class="mb-4">
            <h3 class="text-lg font-semibold mb-2">Баланс</h3>
            <div class="bg-[#2e2e40] p-4 rounded-lg flex justify-between items-center">
                <span class="text-xl font-bold">{{ Auth::user()->balance ?? '0.00' }} ₽</span>
                <form action="/add/demobalance" method="POST">
                    @csrf
                    <button type="submit" class="bg-blue-600 px-4 py-2 rounded-xl hover:bg-blue-700 transition">
                        Пополнить
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-6 flex justify-between">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="bg-red-600 px-4 py-2 rounded-xl hover:bg-red-700 transition">
                    Выйти
                </button>
            </form>
            <a href="/" class="text-blue-400 hover:underline mt-2">← На главную</a>
        </div>
    </div>
</div>
@endsection
