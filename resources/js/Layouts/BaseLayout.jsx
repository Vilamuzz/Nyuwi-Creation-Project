export default function BaseLayout({ children }) {
    return (
        <>
            <header className="bg-gray-800 text-white shadow-md">
                <nav className="container mx-auto flex justify-between items-center py-4 px-6">
                    <div className="text-2xl font-bold">
                        <a href="/home" className="hover:text-gray-300">
                            V
                        </a>
                    </div>
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
                </nav>
            </header>
            <main className="container mx-auto p-6">{children}</main>
        </>
    );
}
