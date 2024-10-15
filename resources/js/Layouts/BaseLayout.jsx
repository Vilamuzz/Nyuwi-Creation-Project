"use client";

import * as React from "react";
import { Button } from "@/components/ui/button"; // Shadcn Button
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"; // Shadcn Dropdown
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar"; // Shadcn Avatar

export default function BaseLayout({ children }) {
    return (
        <>
            {/* Header with Shadcn UI Components */}
            <header className="bg-gray-800 text-white shadow-md">
                <nav className="container mx-auto flex justify-between items-center py-4 px-6">
                    {/* Logo */}
                    <div className="text-2xl font-bold">
                        <a href="/home" className="hover:text-gray-300">
                            V
                        </a>
                    </div>

                    {/* Navigation Links */}
                    <ul className="flex space-x-6">
                        <li>
                            <a href="/home" className="hover:text-gray-300">
                                Home
                            </a>
                        </li>
                        <li>
                            <a href="/about" className="hover:text-gray-300">
                                About
                            </a>
                        </li>
                    </ul>

                    {/* User Profile Dropdown with Avatar */}
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button
                                variant="ghost"
                                className="relative h-8 w-8 rounded-full"
                            >
                                <Avatar className="h-8 w-8">
                                    <AvatarImage
                                        src="/placeholder-user.jpg"
                                        alt="User"
                                    />
                                    <AvatarFallback>U</AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent
                            className="w-56"
                            align="end"
                            forceMount
                        >
                            <DropdownMenuItem>
                                <a href="/profile">Profile</a>
                            </DropdownMenuItem>
                            <DropdownMenuItem>
                                <a href="/settings">Settings</a>
                            </DropdownMenuItem>
                            <DropdownMenuItem>
                                <a href="/logout">Log out</a>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </nav>
            </header>

            {/* Main Content Area */}
            <main className="container mx-auto p-6">{children}</main>
        </>
    );
}
