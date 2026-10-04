<?php

declare(strict_types=1);

namespace Tests;

use App\Config;
use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\DocumentReference;
use Google\Cloud\Firestore\DocumentSnapshot;
use Google\Cloud\Firestore\FirestoreClient;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testGetEnvSuccess(): void
    {
        $snapshotMock = $this->createMock(DocumentSnapshot::class);
        $snapshotMock->method('exists')->willReturn(true);
        $snapshotMock->method('data')->willReturn([
            'api_token' => 'dummy_token',
            'room_id' => '123456',
        ]);

        $docRefMock = $this->createMock(DocumentReference::class);
        $docRefMock->method('snapshot')->willReturn($snapshotMock);

        $collectionMock = $this->createMock(CollectionReference::class);
        $collectionMock->method('document')->with('config')->willReturn($docRefMock);

        $firestoreMock = $this->createMock(FirestoreClient::class);
        $firestoreMock->method('collection')->with('chatwork-memo')->willReturn($collectionMock);

        $config = Config::getEnv($firestoreMock);

        $this->assertSame('dummy_token', $config['api_token']);
        $this->assertSame('123456', $config['room_id']);
    }

    public function testGetEnvThrowsWhenDocumentDoesNotExist(): void
    {
        $snapshotMock = $this->createMock(DocumentSnapshot::class);
        $snapshotMock->method('exists')->willReturn(false);

        $docRefMock = $this->createMock(DocumentReference::class);
        $docRefMock->method('snapshot')->willReturn($snapshotMock);

        $collectionMock = $this->createMock(CollectionReference::class);
        $collectionMock->method('document')->with('config')->willReturn($docRefMock);

        $firestoreMock = $this->createMock(FirestoreClient::class);
        $firestoreMock->method('collection')->with('chatwork-memo')->willReturn($collectionMock);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Firestore に chatwork-memo/config ドキュメントが存在しません。');

        Config::getEnv($firestoreMock);
    }

    public function testGetEnvThrowsWhenDataIncomplete(): void
    {
        $snapshotMock = $this->createMock(DocumentSnapshot::class);
        $snapshotMock->method('exists')->willReturn(true);
        $snapshotMock->method('data')->willReturn([
            'api_token' => 'dummy_token',
            'room_id' => '',
        ]);

        $docRefMock = $this->createMock(DocumentReference::class);
        $docRefMock->method('snapshot')->willReturn($snapshotMock);

        $collectionMock = $this->createMock(CollectionReference::class);
        $collectionMock->method('document')->with('config')->willReturn($docRefMock);

        $firestoreMock = $this->createMock(FirestoreClient::class);
        $firestoreMock->method('collection')->with('chatwork-memo')->willReturn($collectionMock);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Firestore の chatwork-memo/config に api_token または room_id が設定されていません。');

        Config::getEnv($firestoreMock);
    }
}
